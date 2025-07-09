<?php
// Database connection
try {
    $db = new PDO('sqlite:inventory.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Database connection error: " . $e->getMessage();
    exit;
}

// Check Ryzen 5 5600G entry
try {
    $stmt = $db->prepare("SELECT * FROM Inventory WHERE Name LIKE '%5600G%'");
    $stmt->execute();
    $processor = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<h1>Ryzen 5 5600G Database Entry</h1>";
    
    if ($processor) {
        echo "<pre>";
        print_r($processor);
        echo "</pre>";
        
        // Check if the image file exists
        $imagePath = $processor['Image_Path'];
        $fullPath = __DIR__ . '/' . str_replace('../', '', $imagePath);
        
        echo "<h2>Image Path Check</h2>";
        echo "<p>Database path: " . htmlspecialchars($imagePath) . "</p>";
        echo "<p>Full system path: " . htmlspecialchars($fullPath) . "</p>";
        echo "<p>File exists: " . (file_exists($fullPath) ? 'Yes' : 'No') . "</p>";
        
        // Check if the actual image file exists
        $actualFile = __DIR__ . '/assets/images/components/processor/R5 5600g.jpg';
        echo "<p>Actual file path: " . htmlspecialchars($actualFile) . "</p>";
        echo "<p>Actual file exists: " . (file_exists($actualFile) ? 'Yes' : 'No') . "</p>";
        
        // Fix the path
        try {
            $stmt = $db->prepare("UPDATE Inventory SET Image_Path = :path WHERE Name LIKE '%5600G%'");
            $correctPath = '../assets/images/components/processor/R5 5600g.jpg';
            $stmt->bindParam(':path', $correctPath);
            $stmt->execute();
            $updated = $stmt->rowCount();
            
            echo "<h2>Path Update</h2>";
            echo "<p>Updated to: " . htmlspecialchars($correctPath) . "</p>";
            echo "<p>Records updated: " . $updated . "</p>";
        } catch(PDOException $e) {
            echo "<p>Error updating: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p>No processor found with name containing '5600G'</p>";
    }
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Display an image test
echo "<h2>Image Display Test</h2>";
echo "<p>Testing image display with the correct path:</p>";
echo "<img src='../assets/images/components/processor/R5 5600g.jpg' style='max-width: 300px; border: 1px solid #ccc;'>";
?>
