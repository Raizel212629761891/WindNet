<?php
// Database connection
try {
    $db = new PDO('sqlite:inventory.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Database connection error: " . $e->getMessage();
    exit;
}

// Function to check if file exists
function checkFileExists($path) {
    // Remove '../' from the beginning if present
    $localPath = str_replace('../', '', $path);
    // Get the full server path
    $fullPath = __DIR__ . '/' . $localPath;
    return file_exists($fullPath) ? 'Yes' : 'No';
}

// Function to get a list of image files in a directory
function getImagesInDirectory($dir) {
    $fullDir = __DIR__ . '/' . str_replace('../', '', $dir);
    if (!is_dir($fullDir)) {
        return "Directory does not exist: $fullDir";
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

// Get monitor data from database
try {
    $categories = ['Monitor', 'Hard Drive', 'CPU Cooler', 'Graphics Card'];
    
    echo "<html><head><title>Image Path Checker</title>";
    echo "<style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1, h2 { color: #0284c7; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .error { color: red; font-weight: bold; }
        .success { color: green; }
        .directory-list { background-color: #f5f5f5; padding: 10px; border-radius: 5px; margin-bottom: 20px; }
    </style></head><body>";
    
    echo "<h1>Wind Net Image Path Checker</h1>";
    
    foreach ($categories as $category) {
        echo "<h2>$category Images</h2>";
        
        // Check database entries
        $stmt = $db->prepare("SELECT ID, Name, Image FROM Inventory WHERE Category = :category");
        $stmt->bindParam(':category', $category);
        $stmt->execute();
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>Database Entries</h3>";
        echo "<table>";
        echo "<tr><th>ID</th><th>Name</th><th>Image Path</th><th>File Exists</th><th>Issues</th></tr>";
        
        foreach ($items as $item) {
            $imagePath = isset($item['Image']) ? $item['Image'] : '';
            $exists = !empty($imagePath) ? checkFileExists($imagePath) : 'N/A';
            
            // Check for common path issues
            $issues = [];
            if (empty($imagePath)) {
                $issues[] = 'Empty path';
            }
            
            $categoryLower = strtolower($category);
            $doublePathPattern = "components/{$categoryLower}/{$category}/";
            if (strpos($imagePath, $doublePathPattern) !== false) {
                $issues[] = 'Contains duplicate category folder';
            }
            
            if (strpos($imagePath, '\\') !== false) {
                $issues[] = 'Contains backslashes';
            }
            
            echo "<tr>";
            echo "<td>" . htmlspecialchars($item['ID']) . "</td>";
            echo "<td>" . htmlspecialchars($item['Name']) . "</td>";
            echo "<td>" . htmlspecialchars($imagePath) . "</td>";
            echo "<td class='" . ($exists == 'Yes' ? 'success' : 'error') . "'>" . $exists . "</td>";
            echo "<td>" . (empty($issues) ? 'None' : implode(', ', $issues)) . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        
        // Check actual files in directory
        $expectedDir = "assets/images/components/" . strtolower($category);
        $images = getImagesInDirectory($expectedDir);
        
        echo "<h3>Files in Directory</h3>";
        echo "<p>Directory: $expectedDir</p>";
        
        if (is_array($images)) {
            echo "<div class='directory-list'>";
            if (count($images) > 0) {
                echo "<ul>";
                foreach ($images as $image) {
                    echo "<li>" . htmlspecialchars($image) . "</li>";
                }
                echo "</ul>";
            } else {
                echo "<p>No image files found in directory.</p>";
            }
            echo "</div>";
        } else {
            echo "<p class='error'>" . $images . "</p>";
        }
        
        // Check for duplicate directory
        $duplicateDir = "assets/images/components/" . strtolower($category) . "/" . $category;
        $duplicateImages = getImagesInDirectory($duplicateDir);
        
        echo "<h3>Files in Potential Duplicate Directory</h3>";
        echo "<p>Directory: $duplicateDir</p>";
        
        if (is_array($duplicateImages)) {
            echo "<div class='directory-list'>";
            if (count($duplicateImages) > 0) {
                echo "<ul>";
                foreach ($duplicateImages as $image) {
                    echo "<li>" . htmlspecialchars($image) . "</li>";
                }
                echo "</ul>";
            } else {
                echo "<p>No image files found in duplicate directory.</p>";
            }
            echo "</div>";
        } else {
            echo "<p>" . $duplicateImages . "</p>";
        }
    }
    
    echo "<h2>Fix Recommendations</h2>";
    echo "<ol>";
    echo "<li>Update database image paths to match actual file locations</li>";
    echo "<li>Ensure all image files are placed in the correct directories</li>";
    echo "<li>Fix any paths with duplicate category folders</li>";
    echo "<li>Replace backslashes with forward slashes in all paths</li>";
    echo "</ol>";
    
    echo "<p><a href='includes/pc-builder1.php'>Go to PC Builder</a></p>";
    
    echo "</body></html>";
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
