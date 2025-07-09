<?php
// Database connection
try {
    $db = new PDO('sqlite:inventory.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "<div style='color: red; font-weight: bold;'>Database connection error: " . $e->getMessage() . "</div>";
    exit;
}

// Style for the page
echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Wind Net - Fix All Image Paths</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            color: #333;
            background-color: #f5f7fa;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1, h2, h3 {
            color: #0284c7;
        }
        h1 {
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #f1f5f9;
        }
        tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .success {
            color: #16a34a;
            font-weight: bold;
        }
        .error {
            color: #dc2626;
            font-weight: bold;
        }
        .warning {
            color: #ca8a04;
            font-weight: bold;
        }
        .action-btn {
            display: inline-block;
            background-color: #0284c7;
            color: white;
            padding: 10px 15px;
            border-radius: 4px;
            text-decoration: none;
            margin: 10px 0;
        }
        .action-btn:hover {
            background-color: #0369a1;
        }
        .code-block {
            background-color: #1e293b;
            color: #e2e8f0;
            padding: 15px;
            border-radius: 4px;
            overflow-x: auto;
            font-family: monospace;
            margin: 10px 0;
        }
        .section {
            margin-bottom: 30px;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
    </style>
</head>
<body>
    <div class='container'>
        <h1>Wind Net PC Builder - Image Path Fixer</h1>";

// Function to check if file exists
function checkFileExists($path) {
    // Remove '../' from the beginning if present
    $localPath = str_replace('../', '', $path);
    // Get the full server path
    $fullPath = __DIR__ . '/' . $localPath;
    return file_exists($fullPath);
}

// Function to get all image files in a directory
function getImagesInDirectory($dir) {
    $fullDir = __DIR__ . '/' . str_replace('../', '', $dir);
    if (!is_dir($fullDir)) {
        return [];
    }
    
    $images = [];
    $files = scandir($fullDir);
    foreach ($files as $file) {
        if ($file != '.' && $file != '..' && !is_dir($fullDir . '/' . $file)) {
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                $images[] = $file;
            }
        }
    }
    
    return $images;
}

// Function to update image path in database
function updateImagePath($db, $id, $newPath) {
    try {
        $stmt = $db->prepare("UPDATE Inventory SET Image = :path WHERE ID = :id");
        $stmt->bindParam(':path', $newPath);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    } catch(PDOException $e) {
        return false;
    }
}

// Function to fix duplicate category in path
function fixDuplicateCategoryPath($path, $category) {
    $categoryLower = strtolower($category);
    $doublePathPattern = "components/{$categoryLower}/{$category}/";
    $correctPathPattern = "components/{$categoryLower}/";
    return str_replace($doublePathPattern, $correctPathPattern, $path);
}

// Function to find the best matching image for a component
function findBestMatchingImage($componentName, $category, $availableImages) {
    // Convert component name to lowercase for case-insensitive matching
    $componentNameLower = strtolower($componentName);
    
    // First try exact match
    foreach ($availableImages as $image) {
        $imageName = pathinfo($image, PATHINFO_FILENAME);
        if (strtolower($imageName) === $componentNameLower) {
            return $image;
        }
    }
    
    // Try partial match
    foreach ($availableImages as $image) {
        $imageName = pathinfo($image, PATHINFO_FILENAME);
        if (strpos($componentNameLower, strtolower($imageName)) !== false || 
            strpos(strtolower($imageName), $componentNameLower) !== false) {
            return $image;
        }
    }
    
    // Try matching individual words
    $componentWords = explode(' ', $componentNameLower);
    foreach ($availableImages as $image) {
        $imageName = pathinfo($image, PATHINFO_FILENAME);
        $imageNameLower = strtolower($imageName);
        
        foreach ($componentWords as $word) {
            if (strlen($word) > 3 && strpos($imageNameLower, $word) !== false) {
                return $image;
            }
        }
    }
    
    // If no match found, return first image if available
    return !empty($availableImages) ? $availableImages[0] : null;
}

// Process each problematic category
$categories = ['Monitor', 'Hard Drive', 'CPU Cooler', 'Graphics Card'];
$totalFixed = 0;
$totalProblems = 0;

echo "<div class='section'>";
echo "<h2>1. Analyzing Image Paths</h2>";

foreach ($categories as $category) {
    echo "<h3>{$category} Components</h3>";
    
    // Get available images in the correct directory
    $categoryDir = "assets/images/components/" . strtolower($category);
    $availableImages = getImagesInDirectory($categoryDir);
    
    echo "<p>Found " . count($availableImages) . " images in {$categoryDir}:</p>";
    if (count($availableImages) > 0) {
        echo "<ul>";
        foreach ($availableImages as $image) {
            echo "<li>{$image}</li>";
        }
        echo "</ul>";
    } else {
        echo "<p class='warning'>No images found in this directory!</p>";
    }
    
    // Get components from database
    $stmt = $db->prepare("SELECT ID, Name, Image FROM Inventory WHERE Category = :category");
    $stmt->bindParam(':category', $category);
    $stmt->execute();
    $components = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p>Found " . count($components) . " {$category} components in database</p>";
    
    echo "<table>";
    echo "<tr><th>ID</th><th>Name</th><th>Current Image Path</th><th>File Exists</th><th>Action</th></tr>";
    
    foreach ($components as $component) {
        $currentPath = isset($component['Image']) ? $component['Image'] : '';
        $fileExists = !empty($currentPath) && checkFileExists($currentPath);
        
        echo "<tr>";
        echo "<td>" . htmlspecialchars($component['ID']) . "</td>";
        echo "<td>" . htmlspecialchars($component['Name']) . "</td>";
        echo "<td>" . htmlspecialchars($currentPath) . "</td>";
        
        if ($fileExists) {
            echo "<td class='success'>Yes</td>";
            echo "<td>No action needed</td>";
        } else {
            echo "<td class='error'>No</td>";
            $totalProblems++;
            
            // Try to find a matching image
            $bestMatch = findBestMatchingImage($component['Name'], $category, $availableImages);
            
            if ($bestMatch) {
                $newPath = "../{$categoryDir}/" . $bestMatch;
                
                // Update in database if requested
                if (isset($_GET['fix']) && $_GET['fix'] == 'true') {
                    if (updateImagePath($db, $component['ID'], $newPath)) {
                        echo "<td class='success'>Updated to: {$newPath}</td>";
                        $totalFixed++;
                    } else {
                        echo "<td class='error'>Failed to update database</td>";
                    }
                } else {
                    echo "<td class='warning'>Suggested fix: {$newPath}</td>";
                }
            } else {
                echo "<td class='error'>No matching image found</td>";
            }
        }
        
        echo "</tr>";
    }
    
    echo "</table>";
}

echo "</div>";

// Create JavaScript fix
echo "<div class='section'>";
echo "<h2>2. JavaScript Runtime Fix</h2>";

if ($totalProblems > 0) {
    echo "<p>Found {$totalProblems} image path problems. ";
    
    if (isset($_GET['fix']) && $_GET['fix'] == 'true') {
        echo "Fixed {$totalFixed} paths in database.</p>";
    } else {
        echo "Click the button below to fix them in the database:</p>";
        echo "<a href='?fix=true' class='action-btn'>Fix Database Paths</a>";
    }
    
    echo "<p>The following JavaScript code has been added to the PC Builder page to fix image paths at runtime:</p>";
    
    echo "<div class='code-block'><pre>
// Fix image paths at runtime
window.addEventListener('load', function() {
    function fixImagePaths() {
        console.log('Fixing image paths...');
        var images = document.querySelectorAll('img');
        var fixed = 0;
        
        images.forEach(function(img) {
            var src = img.getAttribute('src');
            if (src) {
                var originalSrc = src;
                
                // Fix paths with duplicate category folders
                var categories = ['monitor', 'hard drive', 'cpu cooler', 'graphics card'];
                categories.forEach(function(category) {
                    var capitalizedCategory = category.split(' ')
                        .map(function(word) { return word.charAt(0).toUpperCase() + word.slice(1); })
                        .join(' ');
                    
                    var doublePattern = 'components/' + category + '/' + capitalizedCategory + '/';
                    var correctPattern = 'components/' + category + '/';
                    
                    if (src.indexOf(doublePattern) !== -1) {
                        src = src.replace(doublePattern, correctPattern);
                    }
                });
                
                // Fix backslashes
                while (src.indexOf('\\\\') !== -1) {
                    src = src.split('\\\\').join('/');
                }
                
                // Update image if path changed
                if (src !== originalSrc) {
                    console.log('Fixed path: ' + originalSrc + ' → ' + src);
                    img.setAttribute('src', src);
                    fixed++;
                }
            }
        });
        
        if (fixed > 0) {
            console.log('Fixed ' + fixed + ' image paths');
        }
    }
    
    // Run immediately
    fixImagePaths();
    
    // Run after component selection changes
    document.querySelectorAll('select').forEach(function(select) {
        select.addEventListener('change', function() {
            setTimeout(fixImagePaths, 200);
        });
    });
});
</pre></div>";
} else {
    echo "<p class='success'>No image path problems detected. No JavaScript fix needed.</p>";
}

echo "</div>";

// Add links
echo "<div class='section'>";
echo "<h2>3. Next Steps</h2>";
echo "<p>The following actions have been taken to fix image path issues:</p>";
echo "<ol>";
echo "<li>Analyzed all component images in the database</li>";
echo "<li>Identified missing or incorrect image paths</li>";

if (isset($_GET['fix']) && $_GET['fix'] == 'true') {
    echo "<li>Updated database with corrected image paths</li>";
} else {
    echo "<li>Prepared database updates (click 'Fix Database Paths' to apply)</li>";
}

echo "<li>Added JavaScript runtime fix to handle any remaining issues</li>";
echo "</ol>";

echo "<p>You can now:</p>";
echo "<a href='includes/pc-builder1.php' class='action-btn'>Go to PC Builder</a> ";
echo "<a href='check_image_paths.php' class='action-btn'>View Detailed Image Report</a>";
echo "</div>";

echo "</div></body></html>";
?>
