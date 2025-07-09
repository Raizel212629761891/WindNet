<?php
$servername = "localhost";
$username = "root"; // Palitan ng iyong DB username
$password = ""; // Palitan kung may password ang DB mo
$database = "pc_builder_db"; // Palitan ng pangalan ng iyong database

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
