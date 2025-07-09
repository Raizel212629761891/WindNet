<?php
/**
 * Database Import Script
 * This script imports an SQL file into the SQLite database
 */

// Configuration
$sqlFile = 'includes/inventory.sql';
$dbFile = 'inventory.db';
$backupFile = 'inventory_backup_' . date('Y-m-d_H-i-s') . '.db';

// Display header
echo "<!DOCTYPE html>
<html>
<head>
    <title>Database Import</title>
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
        <h1>Database Import Tool</h1>";

// Step 1: Check if files exist
echo "<div class='step'>
    <h2>Step 1: Checking Files</h2>";

$sqlFileExists = file_exists($sqlFile);
$dbFileExists = file_exists($dbFile);

if (!$sqlFileExists) {
    echo "<p class='error'>Error: SQL file not found at '$sqlFile'</p>";
    echo "<p>Please make sure the SQL file is in the correct location.</p>";
    exit_with_footer();
}

if (!$dbFileExists) {
    echo "<p class='warning'>Warning: Database file not found at '$dbFile'</p>";
    echo "<p>A new database will be created.</p>";
} else {
    echo "<p class='success'>Found existing database file: $dbFile</p>";
}

echo "<p class='success'>Found SQL file: $sqlFile</p>";
echo "</div>";

// Step 2: Create backup if database exists
echo "<div class='step'>
    <h2>Step 2: Creating Backup</h2>";

if ($dbFileExists) {
    if (copy($dbFile, $backupFile)) {
        echo "<p class='success'>Successfully created backup: $backupFile</p>";
    } else {
        echo "<p class='error'>Failed to create backup. Aborting import.</p>";
        exit_with_footer();
    }
} else {
    echo "<p>No existing database to backup.</p>";
}
echo "</div>";

// Step 3: Read SQL file
echo "<div class='step'>
    <h2>Step 3: Reading SQL File</h2>";

$sqlContent = file_get_contents($sqlFile);
if ($sqlContent === false) {
    echo "<p class='error'>Failed to read SQL file.</p>";
    exit_with_footer();
}

// Count the number of SQL statements
$statements = explode(';', $sqlContent);
$statementCount = count($statements) - 1; // Last one is usually empty
echo "<p class='success'>Read SQL file successfully. Found approximately $statementCount statements.</p>";
echo "</div>";

// Step 4: Import SQL into database
echo "<div class='step'>
    <h2>Step 4: Importing to Database</h2>";

try {
    // Connect to database (creates it if it doesn't exist)
    $db = new PDO("sqlite:$dbFile");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p>Connected to database successfully.</p>";
    
    // Begin transaction
    $db->beginTransaction();
    
    // Execute SQL statements
    $successCount = 0;
    $errorCount = 0;
    $errors = [];
    
    foreach ($statements as $i => $statement) {
        $statement = trim($statement);
        if (empty($statement)) continue;
        
        try {
            $db->exec($statement);
            $successCount++;
        } catch (PDOException $e) {
            $errorCount++;
            $errors[] = [
                'statement' => substr($statement, 0, 100) . (strlen($statement) > 100 ? '...' : ''),
                'error' => $e->getMessage()
            ];
        }
    }
    
    // Commit transaction
    $db->commit();
    
    echo "<p class='success'>Successfully executed $successCount statements.</p>";
    
    if ($errorCount > 0) {
        echo "<p class='warning'>Encountered $errorCount errors during import.</p>";
        echo "<details><summary>View Errors</summary><pre>";
        foreach ($errors as $i => $error) {
            echo "Error " . ($i + 1) . ":\n";
            echo "Statement: " . htmlspecialchars($error['statement']) . "\n";
            echo "Error: " . htmlspecialchars($error['error']) . "\n\n";
        }
        echo "</pre></details>";
    }
    
} catch (PDOException $e) {
    echo "<p class='error'>Database connection error: " . htmlspecialchars($e->getMessage()) . "</p>";
    
    // Try to restore from backup if it exists
    if (file_exists($backupFile)) {
        echo "<p>Attempting to restore from backup...</p>";
        if (copy($backupFile, $dbFile)) {
            echo "<p class='success'>Successfully restored from backup.</p>";
        } else {
            echo "<p class='error'>Failed to restore from backup.</p>";
        }
    }
    
    exit_with_footer();
}

echo "</div>";

// Step 5: Verify database
echo "<div class='step'>
    <h2>Step 5: Verifying Database</h2>";

try {
    $db = new PDO("sqlite:$dbFile");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get list of tables
    $stmt = $db->query("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<p class='success'>Database verification successful.</p>";
    echo "<p>Found " . count($tables) . " tables in the database:</p>";
    echo "<ul>";
    foreach ($tables as $table) {
        echo "<li>" . htmlspecialchars($table) . "</li>";
    }
    echo "</ul>";
    
    // Check if Inventory table exists and count rows
    if (in_array('Inventory', $tables)) {
        $stmt = $db->query("SELECT COUNT(*) FROM Inventory");
        $count = $stmt->fetchColumn();
        echo "<p>Inventory table contains $count items.</p>";
    }
    
} catch (PDOException $e) {
    echo "<p class='error'>Database verification error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "</div>";

// Completion
echo "<div class='step'>
    <h2>Import Complete</h2>
    <p class='success'>The database has been successfully updated!</p>
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
