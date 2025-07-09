<?php
// Connect to the SQLite database
$db = new SQLite3('inventory.db');

// Get the table names
$tables = $db->query("SELECT name FROM sqlite_master WHERE type='table'");
echo "<h2>Tables in the database:</h2>";
while ($table = $tables->fetchArray(SQLITE3_ASSOC)) {
    echo "<h3>Table: " . $table['name'] . "</h3>";
    
    // Get the columns for each table
    $columns = $db->query("PRAGMA table_info(" . $table['name'] . ")");
    echo "<ul>";
    while ($column = $columns->fetchArray(SQLITE3_ASSOC)) {
        echo "<li>" . $column['name'] . " (" . $column['type'] . ")</li>";
    }
    echo "</ul>";
    
    // Get a sample row to see the data structure
    $sample = $db->query("SELECT * FROM " . $table['name'] . " LIMIT 1");
    if ($row = $sample->fetchArray(SQLITE3_ASSOC)) {
        echo "<h4>Sample data:</h4>";
        echo "<pre>";
        print_r($row);
        echo "</pre>";
    }
}

// Close the database connection
$db->close();
?>
