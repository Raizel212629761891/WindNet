<?php
// Database connection - using the correct path
try {
    // First try in current directory
    if (file_exists('inventory.db')) {
        $db = new PDO('sqlite:inventory.db');
    } 
    // Then try in parent directory
    elseif (file_exists('../inventory.db')) {
        $db = new PDO('sqlite:../inventory.db');
    }
    // Finally try absolute path
    else {
        $db = new PDO('sqlite:' . __DIR__ . '/inventory.db');
    }
    
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p>Successfully connected to database</p>";
} catch(PDOException $e) {
    echo "Database connection error: " . $e->getMessage();
    exit;
}

// Function to update image path for a specific component by name
function updateComponentImage($db, $componentName, $imagePath) {
    try {
        $stmt = $db->prepare("UPDATE Inventory SET Image_Path = :path WHERE Name LIKE :name");
        $stmt->bindParam(':path', $imagePath);
        $name = "%" . $componentName . "%";
        $stmt->bindParam(':name', $name);
        $stmt->execute();
        return $stmt->rowCount();
    } catch(PDOException $e) {
        return "Error: " . $e->getMessage();
    }
}

// Function to scan a directory for images and map them to components
function scanComponentImages($db, $category, $dirPath) {
    $results = [];
    
    if (!is_dir($dirPath)) {
        return ["error" => "Directory not found: $dirPath"];
    }
    
    // Get all components of this category from the database
    try {
        $stmt = $db->prepare("SELECT Name FROM Inventory WHERE Category = :category");
        $stmt->bindParam(':category', $category);
        $stmt->execute();
        $components = $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch(PDOException $e) {
        return ["error" => "Database error: " . $e->getMessage()];
    }
    
    // Scan the directory for images
    $files = scandir($dirPath);
    $images = [];
    foreach ($files as $file) {
        if ($file != '.' && $file != '..' && !is_dir($dirPath . '/' . $file)) {
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $images[] = [
                    'file' => $file,
                    'name' => pathinfo($file, PATHINFO_FILENAME)
                ];
            }
        }
    }
    
    // Try to match images to components
    $matches = [];
    foreach ($components as $component) {
        $bestMatch = null;
        $bestScore = 0;
        
        foreach ($images as $image) {
            // Calculate similarity between component name and image filename
            $componentLower = strtolower($component);
            $imageNameLower = strtolower($image['name']);
            
            // Check for exact matches or substrings
            if ($componentLower == $imageNameLower) {
                $score = 100; // Exact match
            } elseif (strpos($componentLower, $imageNameLower) !== false) {
                $score = 80; // Image name is in component name
            } elseif (strpos($imageNameLower, $componentLower) !== false) {
                $score = 70; // Component name is in image name
            } else {
                // Calculate similarity score
                similar_text($componentLower, $imageNameLower, $score);
            }
            
            if ($score > $bestScore) {
                $bestScore = $score;
                $bestMatch = $image;
            }
        }
        
        // If we found a good match (score > 40%), use it
        if ($bestMatch && $bestScore > 40) {
            $imagePath = "../assets/images/components/" . strtolower($category) . "/" . $bestMatch['file'];
            $matches[] = [
                'component' => $component,
                'image' => $bestMatch['file'],
                'score' => $bestScore,
                'path' => $imagePath
            ];
            
            // Update the database
            $result = updateComponentImage($db, $component, $imagePath);
            $matches[count($matches) - 1]['updated'] = $result;
        }
    }
    
    return [
        'category' => $category,
        'components' => count($components),
        'images' => count($images),
        'matches' => $matches
    ];
}

// Define component categories and their directories
$categories = [
    'Processor' => __DIR__ . '/assets/images/components/processor',
    'Motherboard' => __DIR__ . '/assets/images/components/motherboard',
    'RAM' => __DIR__ . '/assets/images/components/memory',
    'Storage' => __DIR__ . '/assets/images/components/storage',
    'GPU' => __DIR__ . '/assets/images/components/gpu',
    'Power Supply' => __DIR__ . '/assets/images/components/power supply',
    'Casing' => __DIR__ . '/assets/images/components/case'
];

// Process each category
$results = [];
foreach ($categories as $category => $dirPath) {
    $results[$category] = scanComponentImages($db, $category, $dirPath);
}

// Manual updates for specific components
$manualUpdates = [
    // Processors
    'Ryzen 5 5600G' => '../assets/images/components/processor/R5 5600g.jpg',
    'Ryzen 5 4650G' => '../assets/images/components/processor/Ryzen 5 4650g.jpg',
    
    // Motherboards
    'ASUS ROG STRIX B660-F GAMING WIFI DDR5' => '../assets/images/components/motherboard/B660-F Strix.jpg',
    
    // Memory
    'T-Force Dark Za 32GB (2x16GB) 3600MHz DDR4' => '../assets/images/components/memory/T Force Dark Za 32.jpg',
    
    // GPU
    'Colorful iGame GeForce RTX 4090 Vulcan OC-V' => '../assets/images/components/gpu/Colorful 4090.jpg'
];

echo "<h1>Component Image Update Results</h1>";

// Apply manual updates
echo "<h2>Manual Updates</h2>";
echo "<table border='1' style='border-collapse: collapse; padding: 8px;'>";
echo "<tr><th>Component</th><th>Image Path</th><th>Result</th></tr>";

foreach ($manualUpdates as $component => $path) {
    $result = updateComponentImage($db, $component, $path);
    echo "<tr>";
    echo "<td>" . htmlspecialchars($component) . "</td>";
    echo "<td>" . htmlspecialchars($path) . "</td>";
    echo "<td>" . $result . "</td>";
    echo "</tr>";
}

echo "</table>";

// Display automatic matching results
echo "<h2>Automatic Matching Results</h2>";

foreach ($results as $category => $result) {
    echo "<h3>$category</h3>";
    
    if (isset($result['error'])) {
        echo "<p>Error: " . $result['error'] . "</p>";
        continue;
    }
    
    echo "<p>Found " . $result['components'] . " components and " . $result['images'] . " images</p>";
    
    if (count($result['matches']) > 0) {
        echo "<table border='1' style='border-collapse: collapse; padding: 8px;'>";
        echo "<tr><th>Component</th><th>Image</th><th>Match Score</th><th>Updated</th></tr>";
        
        foreach ($result['matches'] as $match) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($match['component']) . "</td>";
            echo "<td>" . htmlspecialchars($match['image']) . "</td>";
            echo "<td>" . round($match['score'], 1) . "%</td>";
            echo "<td>" . $match['updated'] . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p>No matches found</p>";
    }
}

// Display current image paths
echo "<h2>Current Image Paths</h2>";

$categories = ['Processor', 'Motherboard', 'RAM', 'Storage', 'GPU', 'Power Supply', 'Casing'];

foreach ($categories as $category) {
    echo "<h3>$category</h3>";
    
    try {
        $stmt = $db->prepare("SELECT Name, Image_Path FROM Inventory WHERE Category = :category");
        $stmt->bindParam(':category', $category);
        $stmt->execute();
        $components = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($components) > 0) {
            echo "<table border='1' style='border-collapse: collapse; padding: 8px;'>";
            echo "<tr><th>Component</th><th>Image Path</th><th>File Exists</th><th>Preview</th></tr>";
            
            foreach ($components as $component) {
                $fullPath = __DIR__ . '/' . str_replace('../', '', $component['Image_Path']);
                $exists = file_exists($fullPath) ? 'Yes' : 'No';
                
                echo "<tr>";
                echo "<td>" . htmlspecialchars($component['Name']) . "</td>";
                echo "<td>" . htmlspecialchars($component['Image_Path']) . "</td>";
                echo "<td>" . $exists . "</td>";
                echo "<td>";
                if ($exists) {
                    echo "<img src='" . htmlspecialchars($component['Image_Path']) . "' style='max-width: 100px; max-height: 100px;'>";
                } else {
                    echo "N/A";
                }
                echo "</td>";
                echo "</tr>";
            }
            
            echo "</table>";
        } else {
            echo "<p>No components found</p>";
        }
    } catch(PDOException $e) {
        echo "<p>Error: " . $e->getMessage() . "</p>";
    }
}
?>
