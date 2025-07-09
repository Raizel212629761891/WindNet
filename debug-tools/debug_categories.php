<?php
// Database connection
try {
    $db = new PDO('sqlite:inventory.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Database connection error: " . $e->getMessage();
    exit;
}

echo "<h1>Category Debug Information</h1>";

// Check Monitor category
echo "<h2>Monitor Category</h2>";
$stmt = $db->prepare("SELECT * FROM Inventory WHERE Category = 'Monitor' LIMIT 10");
$stmt->execute();
$monitors = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<p>Found " . count($monitors) . " monitors</p>";
echo "<table border='1'>";
echo "<tr><th>ID</th><th>Name</th><th>Image</th><th>Price</th></tr>";
foreach ($monitors as $item) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($item['ID']) . "</td>";
    echo "<td>" . htmlspecialchars($item['Name']) . "</td>";
    echo "<td>" . htmlspecialchars($item['Image']) . "</td>";
    echo "<td>" . htmlspecialchars($item['Price']) . "</td>";
    echo "</tr>";
}
echo "</table>";

// Check Hard Drive category
echo "<h2>Hard Drive Category</h2>";
$stmt = $db->prepare("SELECT * FROM Inventory WHERE Category = 'Hard Drive' LIMIT 10");
$stmt->execute();
$hardDrives = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<p>Found " . count($hardDrives) . " hard drives</p>";
if (count($hardDrives) > 0) {
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Name</th><th>Image</th><th>Price</th></tr>";
    foreach ($hardDrives as $item) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($item['ID']) . "</td>";
        echo "<td>" . htmlspecialchars($item['Name']) . "</td>";
        echo "<td>" . htmlspecialchars($item['Image']) . "</td>";
        echo "<td>" . htmlspecialchars($item['Price']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    // Check Storage category for hard drives
    echo "<p>No hard drives found in 'Hard Drive' category. Checking 'Storage' category...</p>";
    $stmt = $db->prepare("SELECT * FROM Inventory WHERE Category = 'Storage' LIMIT 20");
    $stmt->execute();
    $storage = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p>Found " . count($storage) . " storage items</p>";
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Name</th><th>Image</th><th>Price</th><th>Type</th></tr>";
    foreach ($storage as $item) {
        $name = strtolower($item['Name']);
        $type = "Unknown";
        if (strpos($name, 'ssd') !== false || strpos($name, 'nvme') !== false) {
            $type = "SSD";
        } else if (strpos($name, 'hdd') !== false) {
            $type = "HDD";
        }
        
        echo "<tr>";
        echo "<td>" . htmlspecialchars($item['ID']) . "</td>";
        echo "<td>" . htmlspecialchars($item['Name']) . "</td>";
        echo "<td>" . htmlspecialchars($item['Image']) . "</td>";
        echo "<td>" . htmlspecialchars($item['Price']) . "</td>";
        echo "<td>" . $type . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// Check working categories for comparison
$workingCategories = ['Processor', 'Motherboard', 'Memory'];
foreach ($workingCategories as $category) {
    echo "<h2>{$category} Category (Working)</h2>";
    $stmt = $db->prepare("SELECT * FROM Inventory WHERE Category = :category LIMIT 5");
    $stmt->bindParam(':category', $category);
    $stmt->execute();
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p>Found " . count($items) . " {$category} items</p>";
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Name</th><th>Image</th><th>Price</th></tr>";
    foreach ($items as $item) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($item['ID']) . "</td>";
        echo "<td>" . htmlspecialchars($item['Name']) . "</td>";
        echo "<td>" . htmlspecialchars($item['Image']) . "</td>";
        echo "<td>" . htmlspecialchars($item['Price']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// Show table structure
echo "<h2>Database Structure</h2>";
$stmt = $db->prepare("PRAGMA table_info(Inventory)");
$stmt->execute();
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<p>Inventory table columns:</p>";
echo "<table border='1'>";
echo "<tr><th>CID</th><th>Name</th><th>Type</th><th>NotNull</th><th>Default</th><th>PK</th></tr>";
foreach ($columns as $col) {
    echo "<tr>";
    foreach ($col as $key => $value) {
        echo "<td>" . htmlspecialchars($value) . "</td>";
    }
    echo "</tr>";
}
echo "</table>";

echo "<p><a href='includes/pc-builder1.php'>Go to PC Builder</a></p>";
?>
