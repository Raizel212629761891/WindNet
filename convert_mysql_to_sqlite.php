<?php
/**
 * MySQL to SQLite Converter
 * This script converts a MySQL dump file to SQLite format and imports it
 */

// Configuration
$mysqlFile = 'includes/inventory.sql';
$sqliteDbFile = 'inventory.db';
$backupFile = 'inventory_backup_' . date('Y-m-d_H-i-s') . '.db';

// Display header
echo "<!DOCTYPE html>
<html>
<head>
    <title>MySQL to SQLite Converter</title>
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
        h1 {
            color: #3b82f6;
            margin-top: 0;
        }
        .step {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #334155;
            border-radius: 6px;
        }
        .step h2 {
            margin-top: 0;
            color: #60a5fa;
        }
        .success {
            color: #10b981;
            font-weight: bold;
        }
        .error {
            color: #ef4444;
            font-weight: bold;
        }
        .warning {
            color: #f59e0b;
            font-weight: bold;
        }
        pre {
            background-color: #0f172a;
            padding: 10px;
            border-radius: 4px;
            overflow-x: auto;
            color: #94a3b8;
            max-height: 300px;
            overflow-y: auto;
        }
        .button {
            display: inline-block;
            background-color: #3b82f6;
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 10px;
        }
        .button:hover {
            background-color: #2563eb;
        }
    </style>
</head>
<body>
    <div class='container'>
        <h1>MySQL to SQLite Converter</h1>";

// Step 1: Check if files exist
echo "<div class='step'>
    <h2>Step 1: Checking Files</h2>";

$mysqlFileExists = file_exists($mysqlFile);
$sqliteDbFileExists = file_exists($sqliteDbFile);

if (!$mysqlFileExists) {
    echo "<p class='error'>Error: MySQL file not found at '$mysqlFile'</p>";
    echo "<p>Please make sure the MySQL dump file is in the correct location.</p>";
    exit_with_footer();
}

if ($sqliteDbFileExists) {
    echo "<p class='success'>Found existing SQLite database: $sqliteDbFile</p>";
    echo "<p>A backup will be created before making any changes.</p>";
} else {
    echo "<p class='warning'>No existing SQLite database found. A new one will be created.</p>";
}

echo "<p class='success'>Found MySQL dump file: $mysqlFile</p>";
echo "</div>";

// Step 2: Create backup if database exists
echo "<div class='step'>
    <h2>Step 2: Creating Backup</h2>";

if ($sqliteDbFileExists) {
    if (copy($sqliteDbFile, $backupFile)) {
        echo "<p class='success'>Successfully created backup: $backupFile</p>";
    } else {
        echo "<p class='error'>Failed to create backup. Aborting conversion.</p>";
        exit_with_footer();
    }
} else {
    echo "<p>No existing database to backup.</p>";
}
echo "</div>";

// Step 3: Read and parse MySQL file
echo "<div class='step'>
    <h2>Step 3: Reading MySQL File</h2>";

$mysqlContent = file_get_contents($mysqlFile);
if ($mysqlContent === false) {
    echo "<p class='error'>Failed to read MySQL file.</p>";
    exit_with_footer();
}

echo "<p class='success'>Successfully read MySQL file. Size: " . number_format(strlen($mysqlContent)) . " bytes</p>";
echo "</div>";

// Step 4: Convert MySQL to SQLite
echo "<div class='step'>
    <h2>Step 4: Converting MySQL to SQLite</h2>";

// Extract CREATE TABLE statement
if (preg_match('/CREATE TABLE `Inventory`\s*\((.*?)\)/s', $mysqlContent, $matches)) {
    $createTableContent = $matches[1];
    
    // Convert MySQL column definitions to SQLite
    $createTableContent = preg_replace('/\s+int NOT NULL AUTO_INCREMENT/', ' INTEGER PRIMARY KEY AUTOINCREMENT', $createTableContent);
    $createTableContent = preg_replace('/\s+varchar\((\d+)\)/', ' TEXT', $createTableContent);
    $createTableContent = preg_replace('/\s+text/', ' TEXT', $createTableContent);
    $createTableContent = preg_replace('/\s+int/', ' INTEGER', $createTableContent);
    $createTableContent = preg_replace('/\s+float/', ' REAL', $createTableContent);
    $createTableContent = preg_replace('/\s+double/', ' REAL', $createTableContent);
    $createTableContent = preg_replace('/\s+decimal\(\d+,\d+\)/', ' REAL', $createTableContent);
    $createTableContent = preg_replace('/\s+datetime/', ' TEXT', $createTableContent);
    $createTableContent = preg_replace('/\s+date/', ' TEXT', $createTableContent);
    $createTableContent = preg_replace('/\s+tinyint\(\d+\)/', ' INTEGER', $createTableContent);
    $createTableContent = preg_replace('/\s+bit\(\d+\)/', ' INTEGER', $createTableContent);
    $createTableContent = preg_replace('/\s+_binary\s+/', ' ', $createTableContent);
    
    // Remove MySQL-specific parts
    $createTableContent = preg_replace('/,\s*PRIMARY KEY\s*\([^)]+\)/', '', $createTableContent);
    $createTableContent = preg_replace('/,\s*KEY\s*`[^`]+`\s*\([^)]+\)/', '', $createTableContent);
    $createTableContent = preg_replace('/,\s*UNIQUE KEY\s*`[^`]+`\s*\([^)]+\)/', '', $createTableContent);
    $createTableContent = preg_replace('/,\s*CONSTRAINT\s*`[^`]+`\s*FOREIGN KEY\s*\([^)]+\)\s*REFERENCES\s*`[^`]+`\s*\([^)]+\)(\s*ON DELETE [A-Z]+)?(\s*ON UPDATE [A-Z]+)?/', '', $createTableContent);
    
    // Create SQLite CREATE TABLE statement
    $sqliteCreateTable = "CREATE TABLE IF NOT EXISTS Inventory (" . $createTableContent . ");";
    
    echo "<p class='success'>Successfully converted CREATE TABLE statement to SQLite format.</p>";
} else {
    echo "<p class='error'>Could not find CREATE TABLE statement for Inventory table.</p>";
    exit_with_footer();
}

// Extract INSERT statements
$insertStatements = [];
if (preg_match_all('/INSERT INTO `Inventory` VALUES\s*\((.*?)\);/s', $mysqlContent, $matches)) {
    $rowCount = count($matches[0]);
    echo "<p class='success'>Found $rowCount INSERT statements.</p>";
    
    // Process each INSERT statement
    foreach ($matches[1] as $rowData) {
        // Fix binary data representation
        $rowData = preg_replace('/_binary\s+\'([^\']*?)\'/', "'$1'", $rowData);
        
        // Handle NULL values
        $rowData = str_replace("''", "NULL", $rowData);
        
        // Create SQLite INSERT statement
        $insertStatements[] = "INSERT INTO Inventory VALUES (" . $rowData . ");";
    }
} else {
    echo "<p class='warning'>Could not find INSERT statements for Inventory table.</p>";
}

echo "<p>Created " . count($insertStatements) . " SQLite INSERT statements.</p>";
echo "</div>";

// Step 5: Import to SQLite database
echo "<div class='step'>
    <h2>Step 5: Importing to SQLite Database</h2>";

try {
    // Connect to SQLite database
    $db = new PDO("sqlite:$sqliteDbFile");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Begin transaction
    $db->beginTransaction();
    
    // Drop existing table if it exists
    $db->exec("DROP TABLE IF EXISTS Inventory;");
    
    // Create table
    $db->exec($sqliteCreateTable);
    echo "<p class='success'>Created Inventory table in SQLite database.</p>";
    
    // Insert data
    $successCount = 0;
    $errorCount = 0;
    $errors = [];
    
    foreach ($insertStatements as $i => $statement) {
        try {
            $db->exec($statement);
            $successCount++;
            
            // Show progress every 100 rows
            if ($successCount % 100 === 0) {
                echo "<p>Imported $successCount rows...</p>";
                ob_flush();
                flush();
            }
        } catch (PDOException $e) {
            $errorCount++;
            $errors[] = [
                'row' => $i + 1,
                'error' => $e->getMessage(),
                'statement' => substr($statement, 0, 100) . (strlen($statement) > 100 ? '...' : '')
            ];
            
            // Only show first 10 errors to avoid overwhelming the output
            if ($errorCount > 10) {
                break;
            }
        }
    }
    
    // Commit transaction
    $db->commit();
    
    echo "<p class='success'>Successfully imported $successCount rows into the SQLite database.</p>";
    
    if ($errorCount > 0) {
        echo "<p class='warning'>Encountered $errorCount errors during import.</p>";
        echo "<details><summary>View First 10 Errors</summary><pre>";
        foreach ($errors as $i => $error) {
            echo "Error in row " . $error['row'] . ":\n";
            echo "Statement: " . htmlspecialchars($error['statement']) . "\n";
            echo "Error: " . htmlspecialchars($error['error']) . "\n\n";
        }
        echo "</pre></details>";
    }
    
} catch (PDOException $e) {
    echo "<p class='error'>Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
    
    // Try to restore from backup if it exists
    if (file_exists($backupFile)) {
        echo "<p>Attempting to restore from backup...</p>";
        if (copy($backupFile, $sqliteDbFile)) {
            echo "<p class='success'>Successfully restored from backup.</p>";
        } else {
            echo "<p class='error'>Failed to restore from backup.</p>";
        }
    }
    
    exit_with_footer();
}

echo "</div>";

// Step 6: Verify database
echo "<div class='step'>
    <h2>Step 6: Verifying Database</h2>";

try {
    $db = new PDO("sqlite:$sqliteDbFile");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get table structure
    $stmt = $db->query("PRAGMA table_info(Inventory);");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p class='success'>Database verification successful.</p>";
    echo "<p>Inventory table has " . count($columns) . " columns:</p>";
    echo "<ul>";
    foreach ($columns as $column) {
        echo "<li>" . htmlspecialchars($column['name']) . " (" . htmlspecialchars($column['type']) . ")</li>";
    }
    echo "</ul>";
    
    // Count rows
    $stmt = $db->query("SELECT COUNT(*) FROM Inventory;");
    $count = $stmt->fetchColumn();
    echo "<p>Inventory table contains $count items.</p>";
    
    // Sample data
    $stmt = $db->query("SELECT * FROM Inventory LIMIT 3;");
    $sampleData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p>Sample data (first 3 rows):</p>";
    echo "<pre>";
    foreach ($sampleData as $row) {
        echo "ID: " . htmlspecialchars($row['Id']) . "\n";
        echo "Category: " . htmlspecialchars($row['Category']) . "\n";
        echo "Status: " . htmlspecialchars($row['Status']) . "\n";
        echo "Name: " . htmlspecialchars($row['Name']) . "\n";
        echo "------------------------------\n";
    }
    echo "</pre>";
    
} catch (PDOException $e) {
    echo "<p class='error'>Database verification error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "</div>";

// Completion
echo "<div class='step'>
    <h2>Conversion Complete</h2>
    <p class='success'>The MySQL dump has been successfully converted and imported into SQLite!</p>
    <p>You can now return to the PC Builder and test the application with the new database.</p>
    <a href='includes/pc-builder1.php' class='button'>Go to PC Builder</a>
</div>";

// Footer function
function exit_with_footer() {
    echo "</div></body></html>";
    exit;
}

// Display footer
echo "</div></body></html>";
?>
