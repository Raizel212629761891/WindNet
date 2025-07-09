<?php
header('Content-Type: text/html; charset=utf-8');
echo "<html><head><title>Inventory Database Analysis</title>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
    tr:nth-child(even) { background-color: #f9f9f9; }
    h1, h2, h3 { color: #333; }
    .sample-data { background-color: #f8f8f8; padding: 10px; border-radius: 5px; overflow: auto; }
</style>";
echo "</head><body>";
echo "<h1>Inventory Database Analysis</h1>";

try {
    // Connect to the SQLite database
    $dbPath = __DIR__ . '/includes/inventory.db';
    echo "<p>Attempting to connect to database at: " . htmlspecialchars($dbPath) . "</p>";
    
    if (!file_exists($dbPath)) {
        echo "<p style='color:red'>Error: Database file not found at " . htmlspecialchars($dbPath) . "</p>";
        exit;
    }
    
    $db = new PDO('sqlite:' . $dbPath);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color:green'>Successfully connected to the database.</p>";
    
    // Get the table names
    $tables = $db->query("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name");
    echo "<h2>Tables in the database:</h2>";
    
    $tableNames = [];
    while ($table = $tables->fetch(PDO::FETCH_ASSOC)) {
        $tableNames[] = $table['name'];
    }
    
    foreach ($tableNames as $tableName) {
        echo "<h3>Table: " . htmlspecialchars($tableName) . "</h3>";
        
        // Get the columns for each table
        $columns = $db->query("PRAGMA table_info(" . $db->quote($tableName) . ")");
        echo "<table>";
        echo "<tr><th>Column Name</th><th>Type</th><th>Not Null</th><th>Default Value</th><th>Primary Key</th></tr>";
        while ($column = $columns->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($column['name']) . "</td>";
            echo "<td>" . htmlspecialchars($column['type']) . "</td>";
            echo "<td>" . ($column['notnull'] ? 'Yes' : 'No') . "</td>";
            echo "<td>" . htmlspecialchars($column['dflt_value'] ?? 'NULL') . "</td>";
            echo "<td>" . ($column['pk'] ? 'Yes' : 'No') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Get row count
        $stmt = $db->prepare("SELECT COUNT(*) FROM " . $tableName);
        $stmt->execute();
        $rowCount = $stmt->fetchColumn();
        echo "<p>Total rows: " . $rowCount . "</p>";
        
        // Get a sample row to see the data structure (only if there's data)
        if ($rowCount > 0) {
            $sample = $db->query("SELECT * FROM " . $tableName . " LIMIT 1");
            if ($row = $sample->fetch(PDO::FETCH_ASSOC)) {
                echo "<h4>Sample data:</h4>";
                echo "<div class='sample-data'><pre>";
                print_r($row);
                echo "</pre></div>";
            }
        }
        
        // Show table statistics
        echo "<h4>Column Statistics:</h4>";
        echo "<table>";
        echo "<tr><th>Column</th><th>Distinct Values</th><th>Null Count</th><th>Min Value</th><th>Max Value</th></tr>";
        
        $columnInfo = $db->query("PRAGMA table_info(" . $db->quote($tableName) . ")");
        while ($column = $columnInfo->fetch(PDO::FETCH_ASSOC)) {
            $columnName = $column['name'];
            echo "<tr>";
            echo "<td>" . htmlspecialchars($columnName) . "</td>";
            
            // Get distinct count
            try {
                $stmt = $db->prepare("SELECT COUNT(DISTINCT " . $columnName . ") FROM " . $tableName);
                $stmt->execute();
                $distinctCount = $stmt->fetchColumn();
                echo "<td>" . $distinctCount . "</td>";
            } catch (Exception $e) {
                echo "<td>Error</td>";
            }
            
            // Get null count
            try {
                $stmt = $db->prepare("SELECT COUNT(*) FROM " . $tableName . " WHERE " . $columnName . " IS NULL");
                $stmt->execute();
                $nullCount = $stmt->fetchColumn();
                echo "<td>" . $nullCount . "</td>";
            } catch (Exception $e) {
                echo "<td>Error</td>";
            }
            
            // Get min value
            try {
                $stmt = $db->prepare("SELECT MIN(" . $columnName . ") FROM " . $tableName);
                $stmt->execute();
                $minValue = $stmt->fetchColumn();
                echo "<td>" . (is_null($minValue) ? 'NULL' : htmlspecialchars($minValue)) . "</td>";
            } catch (Exception $e) {
                echo "<td>Error</td>";
            }
            
            // Get max value
            try {
                $stmt = $db->prepare("SELECT MAX(" . $columnName . ") FROM " . $tableName);
                $stmt->execute();
                $maxValue = $stmt->fetchColumn();
                echo "<td>" . (is_null($maxValue) ? 'NULL' : htmlspecialchars($maxValue)) . "</td>";
            } catch (Exception $e) {
                echo "<td>Error</td>";
            }
            
            echo "</tr>";
        }
        echo "</table>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color:red'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "</body></html>";
?>
