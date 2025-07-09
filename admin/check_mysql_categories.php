<?php
// Include database connection
require_once 'includes/db_connect.php';

// Get database connection
try {
    $db = getDbConnection();
    
    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>MySQL Categories Check</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            table { border-collapse: collapse; width: 100%; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #f2f2f2; }
            tr:nth-child(even) { background-color: #f9f9f9; }
        </style>
    </head>
    <body>
        <h1>MySQL Database Categories</h1>";
    
    // Query to get all categories and count items
    $stmt = $db->query("
        SELECT Category, COUNT(*) as Count, 
        SUM(CASE WHEN Status = 'In Stock' THEN 1 ELSE 0 END) as InStockCount
        FROM Inventory 
        GROUP BY Category 
        ORDER BY Category
    ");
    $categoryData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Categories Overview</h2>";
    echo "<table>
        <tr>
            <th>Category</th>
            <th>Total Items</th>
            <th>In Stock Items</th>
        </tr>";
    
    foreach ($categoryData as $data) {
        echo "<tr>
            <td>{$data['Category']}</td>
            <td>{$data['Count']}</td>
            <td>{$data['InStockCount']}</td>
        </tr>";
    }
    
    echo "</table>";
    
    // Check for specific categories
    $problemCategories = [
        'PSU Extension', 'Fan Set', 'Fan Add-on', 'Webcam', 
        'Microphone', 'Cable Adapters', 'Power Device', 'Printer'
    ];
    
    echo "<h2>Problem Categories Check</h2>";
    echo "<table>
        <tr>
            <th>Problem Category</th>
            <th>Exists in Database?</th>
            <th>Similar Categories</th>
        </tr>";
    
    foreach ($problemCategories as $category) {
        // Check if category exists
        $stmt = $db->prepare("SELECT COUNT(*) FROM Inventory WHERE Category = ?");
        $stmt->execute([$category]);
        $exists = $stmt->fetchColumn() > 0 ? 'Yes' : 'No';
        
        // Find similar categories
        $stmt = $db->query("SELECT DISTINCT Category FROM Inventory WHERE Category LIKE '%" . str_replace(' ', '%', $category) . "%'");
        $similar = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $similarText = implode(', ', $similar);
        
        echo "<tr>
            <td>{$category}</td>
            <td>{$exists}</td>
            <td>{$similarText}</td>
        </tr>";
    }
    
    echo "</table>";
    
    echo "</body></html>";
    
} catch(PDOException $e) {
    echo "Database error: " . $e->getMessage();
}
?>
