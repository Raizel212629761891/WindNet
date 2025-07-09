<?php
// Include database connection
require_once 'includes/db_connect.php';

// Get database connection
try {
    $db = getDbConnection();
    
    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>Database Categories</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                line-height: 1.6;
                margin: 0;
                padding: 20px;
                background-color: #0f172a;
                color: #e2e8f0;
            }
            .container {
                max-width: 800px;
                margin: 0 auto;
                background-color: #1e293b;
                padding: 20px;
                border-radius: 8px;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            }
            h1, h2 {
                color: #3b82f6;
            }
            ul {
                list-style-type: none;
                padding: 0;
            }
            li {
                padding: 8px 12px;
                margin-bottom: 6px;
                background-color: #334155;
                border-radius: 4px;
            }
            .count {
                float: right;
                background-color: #3b82f6;
                color: white;
                padding: 2px 8px;
                border-radius: 12px;
                font-size: 0.8em;
            }
            .category-name {
                font-weight: bold;
                color: #60a5fa;
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <h1>Database Categories</h1>";
    
    echo "<h2>All Categories in Database</h2>";
    
    // Query to get distinct categories
    $stmt = $db->query("SELECT DISTINCT Category FROM Inventory ORDER BY Category");
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<ul>";
    foreach ($categories as $category) {
        // Count items in each category
        $countStmt = $db->prepare("SELECT COUNT(*) FROM Inventory WHERE Category = ?");
        $countStmt->execute([$category]);
        $count = $countStmt->fetchColumn();
        
        echo "<li><span class='category-name'>{$category}</span> <span class='count'>{$count} items</span></li>";
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
        
        echo "<li><span class='category-name'>{$category}</span> <span class='count'>{$count} in-stock items</span></li>";
    }
    echo "</ul>";
    
    echo "<h2>Sample Items from Each Category</h2>";
    
    foreach ($categories as $category) {
        echo "<h3>{$category}</h3>";
        
        // Get sample items from this category
        $sampleStmt = $db->prepare("SELECT Id, Name, Status FROM Inventory WHERE Category = ? LIMIT 3");
        $sampleStmt->execute([$category]);
        $samples = $sampleStmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($samples) > 0) {
            echo "<ul>";
            foreach ($samples as $item) {
                echo "<li>{$item['Name']} ({$item['Status']})</li>";
            }
            echo "</ul>";
        } else {
            echo "<p>No items found in this category.</p>";
        }
    }
    
    echo "</div></body></html>";
    
} catch(PDOException $e) {
    echo "Database error: " . $e->getMessage();
}
?>
