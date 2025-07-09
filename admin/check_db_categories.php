<?php
// Include database connection
require_once 'includes/db_connect.php';

// Get database connection
try {
    $db = getDbConnection();
    
    echo "<h2>Available Categories in Database</h2>";
    
    // Query to get distinct categories
    $stmt = $db->query("SELECT DISTINCT Category FROM Inventory ORDER BY Category");
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<ul>";
    foreach ($categories as $category) {
        // Count items in each category
        $countStmt = $db->prepare("SELECT COUNT(*) FROM Inventory WHERE Category = ?");
        $countStmt->execute([$category]);
        $count = $countStmt->fetchColumn();
        
        echo "<li><strong>{$category}</strong> - {$count} items</li>";
    }
    echo "</ul>";
    
    echo "<h2>Categories with 'In Stock' Items</h2>";
    
    // Query to get categories with in-stock items
    $stmt = $db->query("SELECT DISTINCT Category FROM Inventory WHERE Status = 'In Stock' ORDER BY Category");
    $inStockCategories = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<ul>";
    foreach ($inStockCategories as $category) {
        // Count in-stock items in each category
        $countStmt = $db->prepare("SELECT COUNT(*) FROM Inventory WHERE Category = ? AND Status = 'In Stock'");
        $countStmt->execute([$category]);
        $count = $countStmt->fetchColumn();
        
        echo "<li><strong>{$category}</strong> - {$count} in-stock items</li>";
    }
    echo "</ul>";
    
} catch(PDOException $e) {
    echo "Database error: " . $e->getMessage();
}
?>
