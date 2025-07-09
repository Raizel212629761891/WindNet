<?php
/**
 * Direct Database Import Script
 * This script directly imports data from a SQL file into the SQLite database
 */

// Configuration
$sqlFile = 'includes/inventory.sql';
$dbFile = 'inventory.db';
$backupFile = 'inventory_backup_' . date('Y-m-d_H-i-s') . '.db';

// Display header
echo "<!DOCTYPE html>
<html>
<head>
    <title>Direct Database Import</title>
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
        .progress-bar {
            background-color: #334155;
            height: 20px;
            border-radius: 10px;
            margin: 10px 0;
            overflow: hidden;
        }
        .progress-fill {
            background-color: #3b82f6;
            height: 100%;
            width: 0%;
            transition: width 0.3s;
        }
    </style>
</head>
<body>
    <div class='container'>
        <h1>Direct Database Import Tool</h1>";

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

// Step 3: Analyze SQL file
echo "<div class='step'>
    <h2>Step 3: Analyzing SQL File</h2>";

// Try to read the SQL file
$sqlContent = file_get_contents($sqlFile);
if ($sqlContent === false) {
    echo "<p class='error'>Failed to read SQL file.</p>";
    exit_with_footer();
}

// Check if it's a binary file
$isBinary = false;
if (strpos($sqlContent, "\0") !== false) {
    $isBinary = true;
    echo "<p class='warning'>The SQL file appears to be in binary format.</p>";
} else {
    echo "<p class='success'>The SQL file is in text format.</p>";
}

// Try to detect the SQL dialect
$isMySQL = false;
if (strpos($sqlContent, 'AUTO_INCREMENT') !== false || 
    strpos($sqlContent, 'ENGINE=') !== false ||
    strpos($sqlContent, 'SET ') === 0) {
    $isMySQL = true;
    echo "<p class='warning'>The SQL file appears to be in MySQL format, which may need conversion for SQLite.</p>";
} else {
    echo "<p class='success'>The SQL file format seems compatible with SQLite.</p>";
}

echo "<p>SQL file size: " . number_format(strlen($sqlContent)) . " bytes</p>";
echo "</div>";

// Step 4: Create a new database schema
echo "<div class='step'>
    <h2>Step 4: Creating Database Schema</h2>";

try {
    // Connect to database (creates it if it doesn't exist)
    $db = new PDO("sqlite:$dbFile");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create Inventory table with appropriate schema
    $db->exec("DROP TABLE IF EXISTS Inventory");
    
    $createTableSQL = "CREATE TABLE Inventory (
        Id INTEGER PRIMARY KEY AUTOINCREMENT,
        Category TEXT,
        Status TEXT,
        Name TEXT,
        Description TEXT,
        Details TEXT,
        Quantity INTEGER,
        CostPrice REAL,
        SRPCash REAL,
        SRPInstallment REAL,
        PriceBDOReg REAL,
        PriceBPIReg REAL,
        PriceBDOZero REAL,
        WarrantyMonths INTEGER,
        WarrantyDays INTEGER,
        WarrantyVisits INTEGER,
        IsHidden INTEGER,
        ImagePath TEXT,
        HasLargeImage INTEGER,
        LargeImagePath TEXT,
        URL TEXT,
        Sold INTEGER,
        Reserved INTEGER,
        DateAdded TEXT,
        DateUpdated TEXT,
        IsDeleted INTEGER
    )";
    
    $db->exec($createTableSQL);
    echo "<p class='success'>Successfully created Inventory table in the database.</p>";
    
} catch (PDOException $e) {
    echo "<p class='error'>Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
    exit_with_footer();
}
echo "</div>";

// Step 5: Parse the SQL file and extract data
echo "<div class='step'>
    <h2>Step 5: Extracting Data from SQL File</h2>";

// Function to parse INSERT statements from SQL content
function parseInsertStatements($content) {
    $rows = [];
    
    // Try to match INSERT statements
    if (preg_match_all('/INSERT INTO\s+[`\'"]?Inventory[`\'"]?\s+(?:VALUES|values)\s*\((.*?)\);/s', $content, $matches)) {
        foreach ($matches[1] as $rowData) {
            // Process the row data
            $row = [];
            $inString = false;
            $currentValue = '';
            $quoteChar = '';
            
            for ($i = 0; $i < strlen($rowData); $i++) {
                $char = $rowData[$i];
                
                if (!$inString && ($char === ',' || $i === strlen($rowData) - 1)) {
                    // End of value
                    if ($i === strlen($rowData) - 1 && $char !== ',') {
                        $currentValue .= $char;
                    }
                    
                    // Process the value
                    $currentValue = trim($currentValue);
                    
                    // Handle quoted values
                    if ((substr($currentValue, 0, 1) === "'" && substr($currentValue, -1) === "'") ||
                        (substr($currentValue, 0, 1) === '"' && substr($currentValue, -1) === '"')) {
                        $currentValue = substr($currentValue, 1, -1);
                    }
                    
                    // Handle NULL values
                    if (strtoupper($currentValue) === 'NULL') {
                        $currentValue = null;
                    }
                    
                    // Handle binary data
                    if (strpos($currentValue, '_binary') === 0) {
                        $currentValue = preg_replace('/_binary\s+[\'"]([^\'"]*)[\'"]/', '$1', $currentValue);
                    }
                    
                    $row[] = $currentValue;
                    $currentValue = '';
                } elseif (!$inString && ($char === "'" || $char === '"')) {
                    // Start of string
                    $inString = true;
                    $quoteChar = $char;
                    $currentValue .= $char;
                } elseif ($inString && $char === $quoteChar && $rowData[$i-1] !== '\\') {
                    // End of string
                    $inString = false;
                    $currentValue .= $char;
                } else {
                    // Regular character
                    $currentValue .= $char;
                }
            }
            
            $rows[] = $row;
        }
    }
    
    return $rows;
}

// Parse the SQL content
$rows = parseInsertStatements($sqlContent);
$rowCount = count($rows);

if ($rowCount > 0) {
    echo "<p class='success'>Successfully extracted $rowCount rows from the SQL file.</p>";
    
    // Show sample data
    echo "<p>Sample data (first 3 rows):</p>";
    echo "<pre>";
    for ($i = 0; $i < min(3, $rowCount); $i++) {
        $row = $rows[$i];
        echo "Row " . ($i + 1) . ":\n";
        echo "  ID: " . htmlspecialchars($row[0] ?? 'NULL') . "\n";
        echo "  Category: " . htmlspecialchars($row[1] ?? 'NULL') . "\n";
        echo "  Status: " . htmlspecialchars($row[2] ?? 'NULL') . "\n";
        echo "  Name: " . htmlspecialchars($row[3] ?? 'NULL') . "\n";
        echo "  Description: " . htmlspecialchars($row[4] ?? 'NULL') . "\n";
        echo "------------------------------\n";
    }
    echo "</pre>";
} else {
    echo "<p class='error'>Failed to extract data from the SQL file. No INSERT statements found.</p>";
    
    // Try an alternative approach - read the file line by line
    echo "<p>Attempting alternative parsing method...</p>";
    
    $lines = file($sqlFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $insertLines = [];
    
    foreach ($lines as $line) {
        if (strpos($line, 'INSERT INTO') === 0) {
            $insertLines[] = $line;
        }
    }
    
    if (count($insertLines) > 0) {
        echo "<p class='success'>Found " . count($insertLines) . " INSERT statements using alternative method.</p>";
        
        // Show sample data
        echo "<p>Sample INSERT statements:</p>";
        echo "<pre>";
        for ($i = 0; $i < min(3, count($insertLines)); $i++) {
            echo htmlspecialchars(substr($insertLines[$i], 0, 100)) . "...\n";
        }
        echo "</pre>";
        
        // Try to parse these lines
        $rows = [];
        foreach ($insertLines as $line) {
            if (preg_match('/\((.*?)\);?$/', $line, $matches)) {
                $rowData = $matches[1];
                $values = str_getcsv($rowData, ',', "'");
                $rows[] = $values;
            }
        }
        
        $rowCount = count($rows);
        if ($rowCount > 0) {
            echo "<p class='success'>Successfully extracted $rowCount rows using alternative method.</p>";
        } else {
            echo "<p class='error'>Failed to extract data using alternative method.</p>";
            exit_with_footer();
        }
    } else {
        echo "<p class='error'>No INSERT statements found in the file.</p>";
        exit_with_footer();
    }
}
echo "</div>";

// Step 6: Import data to SQLite
echo "<div class='step'>
    <h2>Step 6: Importing Data to SQLite</h2>";

try {
    // Connect to database
    $db = new PDO("sqlite:$dbFile");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Begin transaction
    $db->beginTransaction();
    
    // Prepare insert statement
    $stmt = $db->prepare("INSERT INTO Inventory (
        Id, Category, Status, Name, Description, Details, Quantity, 
        CostPrice, SRPCash, SRPInstallment, PriceBDOReg, PriceBPIReg, 
        PriceBDOZero, WarrantyMonths, WarrantyDays, WarrantyVisits, 
        IsHidden, ImagePath, HasLargeImage, LargeImagePath, URL, 
        Sold, Reserved, DateAdded, DateUpdated, IsDeleted
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    // Insert data
    $successCount = 0;
    $errorCount = 0;
    $errors = [];
    
    echo "<div class='progress-bar'><div class='progress-fill' id='progressFill'></div></div>";
    echo "<p id='progressText'>0% complete (0/$rowCount rows)</p>";
    
    // Flush output to show progress bar
    ob_flush();
    flush();
    
    foreach ($rows as $i => $row) {
        try {
            // Ensure we have the right number of columns
            $params = array_pad($row, 26, null);
            
            // Execute insert
            $stmt->execute($params);
            $successCount++;
            
            // Update progress every 10 rows
            if ($successCount % 10 === 0 || $successCount === $rowCount) {
                $percentage = round(($successCount / $rowCount) * 100);
                echo "<script>
                    document.getElementById('progressFill').style.width = '$percentage%';
                    document.getElementById('progressText').textContent = '$percentage% complete ($successCount/$rowCount rows)';
                </script>";
                ob_flush();
                flush();
            }
        } catch (PDOException $e) {
            $errorCount++;
            $errors[] = [
                'row' => $i + 1,
                'error' => $e->getMessage()
            ];
            
            // Only store the first 10 errors
            if (count($errors) >= 10) {
                break;
            }
        }
    }
    
    // Commit transaction
    $db->commit();
    
    echo "<p class='success'>Successfully imported $successCount out of $rowCount rows.</p>";
    
    if ($errorCount > 0) {
        echo "<p class='warning'>Encountered $errorCount errors during import.</p>";
        echo "<details><summary>View First 10 Errors</summary><pre>";
        foreach ($errors as $error) {
            echo "Error in row " . $error['row'] . ": " . htmlspecialchars($error['error']) . "\n";
        }
        echo "</pre></details>";
    }
    
} catch (PDOException $e) {
    echo "<p class='error'>Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
    
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

// Step 7: Verify database
echo "<div class='step'>
    <h2>Step 7: Verifying Database</h2>";

try {
    $db = new PDO("sqlite:$dbFile");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Count rows
    $stmt = $db->query("SELECT COUNT(*) FROM Inventory");
    $count = $stmt->fetchColumn();
    
    echo "<p class='success'>Database verification successful.</p>";
    echo "<p>Inventory table contains $count items.</p>";
    
    // Get sample data
    $stmt = $db->query("SELECT * FROM Inventory LIMIT 3");
    $sampleData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p>Sample data from database:</p>";
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
