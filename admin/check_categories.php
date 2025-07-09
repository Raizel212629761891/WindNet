<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Get the absolute path to the database
$dbPath = __DIR__ . '/database/inventory.db';

// Check if database file exists
if (!file_exists($dbPath)) {
    echo "Database file not found at: " . $dbPath;
    exit;
}

// Database connection
try {
    $db = new PDO('sqlite:' . $dbPath);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get all distinct categories
    $stmt = $db->query("SELECT DISTINCT Category FROM Inventory");
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<h2>Available Categories in Database:</h2>";
    echo "<ul>";
    foreach ($categories as $category) {
        echo "<li>" . htmlspecialchars($category) . "</li>";
    }
    echo "</ul>";
    
    // Check specifically for CPU Cooler and Monitor
    $coolerStmt = $db->prepare("SELECT COUNT(*) FROM Inventory WHERE Category = 'CPU Cooler'");
    $coolerStmt->execute();
    $coolerCount = $coolerStmt->fetchColumn();
    
    $monitorStmt = $db->prepare("SELECT COUNT(*) FROM Inventory WHERE Category = 'Monitor'");
    $monitorStmt->execute();
    $monitorCount = $monitorStmt->fetchColumn();
    
    echo "<h2>Specific Category Counts:</h2>";
    echo "<p>CPU Cooler items: " . $coolerCount . "</p>";
    echo "<p>Monitor items: " . $monitorCount . "</p>";
    
    // Get a sample of items from these categories
    if ($coolerCount > 0) {
        $sampleCoolerStmt = $db->prepare("SELECT * FROM Inventory WHERE Category = 'CPU Cooler' LIMIT 3");
        $sampleCoolerStmt->execute();
        $sampleCoolers = $sampleCoolerStmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>Sample CPU Coolers:</h3>";
        echo "<pre>";
        print_r($sampleCoolers);
        echo "</pre>";
    }
    
    if ($monitorCount > 0) {
        $sampleMonitorStmt = $db->prepare("SELECT * FROM Inventory WHERE Category = 'Monitor' LIMIT 3");
        $sampleMonitorStmt->execute();
        $sampleMonitors = $sampleMonitorStmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>Sample Monitors:</h3>";
        echo "<pre>";
        print_r($sampleMonitors);
        echo "</pre>";
    }
    
} catch(PDOException $e) {
    echo "Database error: " . $e->getMessage();
}
?>
