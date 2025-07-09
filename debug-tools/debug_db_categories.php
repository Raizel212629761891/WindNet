<?php
// Include database connection
require_once 'includes/db_connect.php';

// Function to print categories in a readable format
function printCategories($categories) {
    foreach ($categories as $category) {
        echo "'{$category}' => ['label' => '{$category}', 'icon' => 'box'],<br>";
    }
}

// Get database connection
try {
    $db = getDbConnection();
    
    echo "<h2>All Categories in Database</h2>";
    
    // Query to get distinct categories
    $stmt = $db->query("SELECT DISTINCT Category FROM Inventory ORDER BY Category");
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    printCategories($categories);
    
} catch(PDOException $e) {
    echo "Database error: " . $e->getMessage();
}
?>
