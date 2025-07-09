<?php
session_start();
include 'db_connect.php';

$user_id = $_SESSION['user_id'] ?? null;
$total_wattage = 0;

if ($user_id && isset($conn)) {
    $stmt = $conn->prepare("SELECT SUM(Watts) AS total_wattage FROM inventory WHERE component_id IN (SELECT component_id FROM user_build WHERE user_id = ?)");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $total_wattage = $row['total_wattage'] ?? 0;
}

echo json_encode(['total_wattage' => $total_wattage]);
