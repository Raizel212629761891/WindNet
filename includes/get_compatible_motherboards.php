<?php
// Database connection
try {
    $db = new PDO('sqlite:inventory.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()]);
    exit;
}

// Get CPU name from request
$cpuName = isset($_GET['cpu']) ? $_GET['cpu'] : '';

if (empty($cpuName)) {
    header('Content-Type: application/json');
    echo json_encode([]);
    exit;
}

try {
    // Determine socket type based on CPU name
    $socket = '';
    $cpuLower = strtolower($cpuName);
    
    // Check if it's AMD Ryzen
    if (strpos($cpuLower, 'ryzen') !== false) {
        // AM4 for Ryzen 3000, 4000, 5000 series
        if (preg_match('/(3|4|5)\d{3}/', $cpuLower)) {
            $socket = 'AM4';
        }
        // AM5 for Ryzen 7000 series
        else if (preg_match('/7\d{3}/', $cpuLower)) {
            $socket = 'AM5';
        }
    }
    // Check if it's Intel
    else if (strpos($cpuLower, 'i3') !== false || 
             strpos($cpuLower, 'i5') !== false || 
             strpos($cpuLower, 'i7') !== false || 
             strpos($cpuLower, 'i9') !== false) {
        // LGA 1700 for 12th and 13th gen
        if (preg_match('/(12|13)\d{3}/', $cpuLower)) {
            $socket = 'LGA1700';
        }
        // LGA 1200 for 10th and 11th gen
        else if (preg_match('/(10|11)\d{3}/', $cpuLower)) {
            $socket = 'LGA1200';
        }
    }

    if ($socket) {
        // Get compatible motherboards
        $stmt = $db->prepare("SELECT * FROM Inventory WHERE Category = 'Motherboard' AND Stock_QTY > 0 AND specs LIKE :socket");
        $stmt->bindParam(':socket', '%' . $socket . '%');
        $stmt->execute();
        $motherboards = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        header('Content-Type: application/json');
        echo json_encode($motherboards);
    } else {
        // If no socket could be determined, return empty array
        header('Content-Type: application/json');
        echo json_encode([]);
    }
} catch(PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()]);
}
?>
