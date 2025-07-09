<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Database Test</h1>";

// Get the absolute path to the database
$dbPath = __DIR__ . '/inventory.db';
echo "<p>Database path: " . htmlspecialchars($dbPath) . "</p>";
echo "<p>Database exists: " . (file_exists($dbPath) ? "Yes" : "No") . "</p>";

// Database connection
try {
    $db = new PDO('sqlite:' . $dbPath);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color:green'>Database connection successful!</p>";
    
    // Check tables
    $tables = $db->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);
    echo "<h2>Tables in database:</h2>";
    echo "<ul>";
    if (count($tables) > 0) {
        foreach ($tables as $table) {
            echo "<li>" . htmlspecialchars($table) . "</li>";
        }
    } else {
        echo "<li>No tables found</li>";
    }
    echo "</ul>";
    
    // If Inventory table exists, show a sample
    if (in_array('Inventory', $tables)) {
        echo "<h2>Sample data from Inventory table:</h2>";
        $stmt = $db->query("SELECT * FROM Inventory LIMIT 5");
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($data) > 0) {
            echo "<table border='1' cellpadding='5'>";
            // Headers
            echo "<tr>";
            foreach (array_keys($data[0]) as $header) {
                echo "<th>" . htmlspecialchars($header) . "</th>";
            }
            echo "</tr>";
            
            // Data rows
            foreach ($data as $row) {
                echo "<tr>";
                foreach ($row as $value) {
                    echo "<td>" . htmlspecialchars($value) . "</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
            
            // Show categories
            $categories = $db->query("SELECT DISTINCT Category FROM Inventory")->fetchAll(PDO::FETCH_COLUMN);
            echo "<h2>Available Categories:</h2>";
            echo "<ul>";
            foreach ($categories as $category) {
                echo "<li>" . htmlspecialchars($category) . "</li>";
            }
            echo "</ul>";
        } else {
            echo "<p>No data in Inventory table</p>";
        }
    }
    
} catch(PDOException $e) {
    echo "<p style='color:red'>Database connection error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
