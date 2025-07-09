<?php
// Database connection file
$dbPath = __DIR__ . '/../inventory.db';

try {
    $db = new PDO('sqlite:' . $dbPath);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo '<div class="bg-red-500 p-4 mb-4 rounded-lg text-white">Database connection error: ' . $e->getMessage() . '</div>';
}
?>