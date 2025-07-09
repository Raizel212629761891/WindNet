<?php
// Include database connection
require_once 'includes/db_connect.php';

// Get database connection
try {
    $db = getDbConnection();
    
    echo "<h2>Categories in Database</h2>";
    
    // Query to get distinct categories
    $stmt = $db->query("SELECT DISTINCT Category FROM Inventory ORDER BY Category");
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<pre>";
    print_r($categories);
    echo "</pre>";
    
} catch(PDOException $e) {
    echo "Database error: " . $e->getMessage();
}
?>
