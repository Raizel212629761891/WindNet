<?php
// Database connection
try {
    $db = new PDO('sqlite:inventory.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Database connection error: " . $e->getMessage();
    exit;
}

// Get CPUs
echo "<h2>CPUs and their specs:</h2>";
try {
    $stmt = $db->prepare("SELECT Name, Specs FROM Inventory WHERE Category = 'Processor' AND Stock_QTY > 0");
    $stmt->execute();
    $cpus = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>CPU Name</th><th>Specs</th></tr>";
    foreach ($cpus as $cpu) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($cpu['Name']) . "</td>";
        echo "<td>" . htmlspecialchars($cpu['Specs']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} catch(PDOException $e) {
    echo "Error getting CPUs: " . $e->getMessage();
}

// Get Motherboards
echo "<h2>Motherboards and their specs:</h2>";
try {
    $stmt = $db->prepare("SELECT Name, Specs FROM Inventory WHERE Category = 'Motherboard' AND Stock_QTY > 0");
    $stmt->execute();
    $motherboards = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Motherboard Name</th><th>Specs</th></tr>";
    foreach ($motherboards as $mb) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($mb['Name']) . "</td>";
        echo "<td>" . htmlspecialchars($mb['Specs']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} catch(PDOException $e) {
    echo "Error getting motherboards: " . $e->getMessage();
}
?>
