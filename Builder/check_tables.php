<?php
// Enable detailed error display
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include database connection
require_once 'db_connect.php';

// Function to show table details
function showTableDetails($tableName) {
    try {
        $db = getDbConnection();
        
        // Get table structure
        $sql = "DESCRIBE " . $tableName;
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $fields = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>Table: {$tableName}</h3>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        
        foreach ($fields as $field) {
            echo "<tr>";
            echo "<td>" . $field['Field'] . "</td>";
            echo "<td>" . $field['Type'] . "</td>";
            echo "<td>" . $field['Null'] . "</td>";
            echo "<td>" . $field['Key'] . "</td>";
            echo "<td>" . ($field['Default'] === NULL ? 'NULL' : $field['Default']) . "</td>";
            echo "<td>" . $field['Extra'] . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        
    } catch (PDOException $e) {
        echo "<p style='color:red'>Database Error: " . $e->getMessage() . "</p>";
    }
}

// HTML Output
?>
<!DOCTYPE html>
<html>
<head>
    <title>Database Table Check</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f4f4f4; }
        h1, h2, h3 { color: #333; }
        table { border-collapse: collapse; margin-bottom: 20px; background: white; }
        th { background: #444; color: white; }
        tr:nth-child(even) { background: #f9f9f9; }
        .container { max-width: 1200px; margin: 0 auto; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Database Table Structure</h1>
        
        <?php
        // Check quotation table
        showTableDetails('quotation');
        
        // Check quotation_items table
        showTableDetails('quotation_items');
        ?>
        
        <h2>Test Insert Quotation</h2>
        <?php
        // Test basic insertion
        try {
            $db = getDbConnection();
            $db->beginTransaction();
            
            // Insert test data into quotation
            $sql = "INSERT INTO quotation (customer_name, customer_contact, notes, final_price, total_discount) 
                    VALUES ('Test Customer', '123456789', 'Test Note', 1000, 200)";
            $db->exec($sql);
            $quotationId = $db->lastInsertId();
            
            echo "<p>Successfully inserted test quotation with ID: {$quotationId}</p>";
            
            // Insert test data into quotation_items
            $sql = "INSERT INTO quotation_items (quotation_id, inventory_id, quantity, price) 
                    VALUES ({$quotationId}, 1, 1, 1000)";
            $db->exec($sql);
            
            echo "<p>Successfully inserted test quotation item</p>";
            
            // Rollback the transaction so we don't actually insert test data
            $db->rollBack();
            echo "<p>Test transaction rolled back - no data was permanently inserted</p>";
            
        } catch (PDOException $e) {
            echo "<p style='color:red'>Test Insert Error: " . $e->getMessage() . "</p>";
            if ($db && $db->inTransaction()) {
                $db->rollBack();
            }
        }
        ?>
    </div>
</body>
</html>
