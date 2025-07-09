<?php
/**
 * Database Connection File
 * This file handles the connection to the MySQL database
 */

// MySQL Database Configuration
$db_config = [
    'host' => 'localhost',
    'username' => 'root',
    'password' => '',
    'database' => 'inventory'
];

/**
 * Get database connection
 * @return PDO Database connection
 */
function getDbConnection() {
    global $db_config;
    
    try {
        // Create MySQL PDO connection
        $dsn = "mysql:host={$db_config['host']};dbname={$db_config['database']};charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ];
        
        $db = new PDO($dsn, $db_config['username'], $db_config['password'], $options);
        return $db;
    } catch (PDOException $e) {
        // Log the error
        error_log("Database Connection Error: " . $e->getMessage());
        
        // Display user-friendly error
        die("Database connection failed. Please contact the administrator.");
    }
}
?>
