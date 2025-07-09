<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Component Image Path Fixer</h1>";

// Check if component image directories exist, create them if not
$componentTypes = ['RAM', 'GPU', 'Processor', 'Motherboard', 'Storage', 'SSD', 'HDD', 'Casing', 'Power Supply', 'CPU Cooler', 'Monitor'];
$baseDir = __DIR__ . '/assets/images/components';

echo "<h2>Creating Component Image Directories</h2>";
echo "<ul>";

foreach ($componentTypes as $type) {
    $dir = $baseDir . '/' . strtolower($type);
    if (!file_exists($dir)) {
        if (mkdir($dir, 0777, true)) {
            echo "<li>Created directory: " . htmlspecialchars($dir) . "</li>";
        } else {
            echo "<li>Failed to create directory: " . htmlspecialchars($dir) . "</li>";
        }
    } else {
        echo "<li>Directory already exists: " . htmlspecialchars($dir) . "</li>";
    }
}

echo "</ul>";

// Create placeholder images for each component type
echo "<h2>Creating Placeholder Images</h2>";
echo "<ul>";

foreach ($componentTypes as $type) {
    $placeholderPath = $baseDir . '/' . strtolower($type) . '/default.png';
    
    if (!file_exists($placeholderPath)) {
        // Create a simple placeholder image
        $width = 300;
        $height = 300;
        $image = imagecreatetruecolor($width, $height);
        
        // Set background color (dark gray)
        $bgColor = imagecolorallocate($image, 40, 40, 40);
        imagefill($image, 0, 0, $bgColor);
        
        // Add component type text
        $textColor = imagecolorallocate($image, 200, 200, 200);
        $text = $type;
        $font = 5; // Built-in font
        
        // Calculate text position to center it
        $textBox = imagettfbbox(20, 0, $font, $text);
        $textWidth = $textBox[2] - $textBox[0];
        $textHeight = $textBox[7] - $textBox[1];
        $textX = ($width - $textWidth) / 2;
        $textY = ($height - $textHeight) / 2;
        
        // Add text to image
        imagestring($image, $font, $textX, $textY, $text, $textColor);
        
        // Save the image
        if (imagepng($image, $placeholderPath)) {
            echo "<li>Created placeholder image: " . htmlspecialchars($placeholderPath) . "</li>";
        } else {
            echo "<li>Failed to create placeholder image: " . htmlspecialchars($placeholderPath) . "</li>";
        }
        
        imagedestroy($image);
    } else {
        echo "<li>Placeholder image already exists: " . htmlspecialchars($placeholderPath) . "</li>";
    }
}

echo "</ul>";

// Update image paths in the database
echo "<h2>Updating Image Paths in Database</h2>";

// Database connection
try {
    $dbPath = __DIR__ . '/includes/inventory.db';
    
    if (!file_exists($dbPath)) {
        echo "<p>Database file not found at: " . htmlspecialchars($dbPath) . "</p>";
        exit;
    }
    
    $db = new PDO('sqlite:' . $dbPath);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get all items with image paths
    $stmt = $db->query("SELECT ID, Category, Name, Image FROM Inventory");
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p>Found " . count($items) . " items in the database.</p>";
    
    $updateCount = 0;
    $errorCount = 0;
    
    // Update each item's image path
    foreach ($items as $item) {
        $category = $item['Category'];
        $id = $item['ID'];
        $name = $item['Name'];
        $currentImage = $item['Image'];
        
        // Skip items that already have correct path format
        if (strpos($currentImage, '../assets/images/components/') === 0) {
            continue;
        }
        
        // Map category to directory name
        $dirMap = [
            'RAM' => 'ram',
            'Processor' => 'processor',
            'Motherboard' => 'motherboard',
            'Storage' => 'storage',
            'GPU' => 'gpu',
            'Power Supply' => 'power supply',
            'Casing' => 'case',
            'CPU Cooler' => 'cpu cooler',
            'Monitor' => 'monitor'
        ];
        
        $dir = isset($dirMap[$category]) ? $dirMap[$category] : strtolower($category);
        
        // Create a valid filename from the item name
        $filename = preg_replace('/[^a-zA-Z0-9]/', '_', $name);
        $filename = strtolower($filename) . '.png';
        
        // New image path
        $newImagePath = '../assets/images/components/' . $dir . '/' . $filename;
        
        // Update the database
        try {
            $updateStmt = $db->prepare("UPDATE Inventory SET Image = ? WHERE ID = ?");
            $updateStmt->execute([$newImagePath, $id]);
            $updateCount++;
            echo "<p>Updated image path for " . htmlspecialchars($name) . " to " . htmlspecialchars($newImagePath) . "</p>";
        } catch (PDOException $e) {
            echo "<p>Error updating image path for " . htmlspecialchars($name) . ": " . $e->getMessage() . "</p>";
            $errorCount++;
        }
    }
    
    echo "<p>Updated $updateCount items. Encountered $errorCount errors.</p>";
    
} catch (PDOException $e) {
    echo "<p>Database error: " . $e->getMessage() . "</p>";
}

// Fix the 'gpu' icon in Lucide
echo "<h2>Fixing GPU Icon</h2>";

// Update the JavaScript file to fix the GPU icon
$jsFilePath = __DIR__ . '/includes/pc-builder.js';
if (file_exists($jsFilePath)) {
    $jsContent = file_get_contents($jsFilePath);
    
    // Check if the file contains the error message about 'gpu' icon
    if (strpos($jsContent, 'data-lucide="gpu"') !== false) {
        // Replace 'gpu' with 'cpu' or another valid icon
        $jsContent = str_replace('data-lucide="gpu"', 'data-lucide="monitor"', $jsContent);
        
        if (file_put_contents($jsFilePath, $jsContent)) {
            echo "<p>Successfully updated the GPU icon in pc-builder.js</p>";
        } else {
            echo "<p>Failed to update the GPU icon in pc-builder.js</p>";
        }
    } else {
        echo "<p>No 'gpu' icon references found in pc-builder.js</p>";
    }
} else {
    echo "<p>JavaScript file not found at: " . htmlspecialchars($jsFilePath) . "</p>";
}

echo "<h2>Fix Complete!</h2>";
echo "<p>Please refresh the PC Builder page to see the fixed images.</p>";
?>
