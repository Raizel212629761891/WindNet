<?php
// get_mobos.php

$db = new PDO('sqlite:C:/xampp/htdocs/Wind Net/includes/inventory.db');

$cpuName = $_GET['cpu'] ?? '';

if (!$cpuName) {
    echo json_encode([]);
    exit;
}

$stmt = $db->prepare("SELECT Specs FROM Inventory WHERE Category = 'CPU' AND Name = ?");
$stmt->execute([$cpuName]);
$cpuSpecs = $stmt->fetchColumn();

if (!$cpuSpecs) {
    echo json_encode([]);
    exit;
}

if (preg_match('/(AM4|AM5|LGA1700|LGA1200|FM2\+|TR4|sTRX4)/i', $cpuSpecs, $matches)) {
    $socketType = $matches[1];

    $stmt = $db->prepare("SELECT Name FROM Inventory WHERE Category = 'Motherboard' AND Specs LIKE ?");
    $stmt->execute(['%' . $socketType . '%']);
    $mobos = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo json_encode($mobos);
} else {
    echo json_encode([]);
}
?>
