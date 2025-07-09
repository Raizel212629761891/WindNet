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

// Function to update image path for a specific processor
function updateProcessorImage($db, $processorName, $imagePath) {
    try {
        $stmt = $db->prepare("UPDATE Inventory SET Image_Path = :path WHERE Name LIKE :name");
        $stmt->bindParam(':path', $imagePath);
        $name = "%" . $processorName . "%";
        $stmt->bindParam(':name', $name);
        $stmt->execute();
        return $stmt->rowCount();
    } catch(PDOException $e) {
        return "Error: " . $e->getMessage();
    }
}

// Update the Ryzen 5 5600G image path
$result = updateProcessorImage($db, "5600G", "../assets/images/components/processor/R5 5600g.jpg");

echo "<h1>Fixed Ryzen 5 5600G Image Path</h1>";
echo "<p>Records updated: $result</p>";

// Check current image paths for processors
echo "<h2>Current Processor Image Paths:</h2>";
try {
    $stmt = $db->prepare("SELECT Name, Image_Path FROM Inventory WHERE Category = 'Processor'");
    $stmt->execute();
    $processors = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; padding: 8px;'>";
    echo "<tr><th>Processor</th><th>Image Path</th><th>File Exists</th></tr>";
    
    foreach ($processors as $proc) {
        $fullPath = __DIR__ . '/' . str_replace('../', '', $proc['Image_Path']);
        $exists = file_exists($fullPath) ? 'Yes' : 'No';
        
        echo "<tr>";
        echo "<td>" . htmlspecialchars($proc['Name']) . "</td>";
        echo "<td>" . htmlspecialchars($proc['Image_Path']) . "</td>";
        echo "<td>" . $exists . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Display test images
echo "<h2>Image Display Test</h2>";

// Test 5600G image
echo "<h3>Ryzen 5 5600G Image Test</h3>";
echo "<img src='../assets/images/components/processor/R5 5600g.jpg' style='max-width: 300px; border: 1px solid #ccc;'>";

// Test 4650G image
echo "<h3>Ryzen 5 4650G Image Test</h3>";
echo "<img src='../assets/images/components/processor/Ryzen 5 4650g.jpg' style='max-width: 300px; border: 1px solid #ccc;'>";
?>
