<?php
// Database connection
try {
    $db = new PDO('sqlite:inventory.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Database connection error: " . $e->getMessage();
    exit;
}

// Get the Intel i5 11400 CPU
echo "<h2>Intel i5 11400 Specs:</h2>";
try {
    $stmt = $db->prepare("SELECT Name, Specs FROM Inventory WHERE Category = 'Processor' AND Name LIKE '%i5 11400%' AND Stock_QTY > 0");
    $stmt->execute();
    $cpu = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($cpu) {
        echo "<p><strong>Name:</strong> " . htmlspecialchars($cpu['Name']) . "</p>";
        echo "<p><strong>Specs:</strong> " . htmlspecialchars($cpu['Specs']) . "</p>";
        
        // Extract socket type
        $socketType = "LGA1200"; // Default for 11th gen Intel
        if (preg_match('/LGA\s*\d+/i', $cpu['Specs'], $matches)) {
            $socketType = $matches[0];
        }
        echo "<p><strong>Socket Type:</strong> " . htmlspecialchars($socketType) . "</p>";
    } else {
        echo "<p>Intel i5 11400 not found in inventory.</p>";
    }
} catch(PDOException $e) {
    echo "Error getting CPU: " . $e->getMessage();
}

// Get all LGA motherboards
echo "<h2>All LGA Motherboards in Inventory:</h2>";
try {
    $stmt = $db->prepare("SELECT Name, Specs FROM Inventory WHERE Category = 'Motherboard' AND (Specs LIKE '%LGA%' OR Specs LIKE '%Socket%') AND Stock_QTY > 0");
    $stmt->execute();
    $motherboards = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Motherboard Name</th><th>Specs</th><th>Compatible with i5 11400?</th><th>Reason</th></tr>";
    
    foreach ($motherboards as $mb) {
        $specs = strtolower($mb['Specs']);
        $name = strtolower($mb['Name']);
        
        // Check compatibility
        $compatible = false;
        $reason = "";
        
        // LGA1200 is for 10th and 11th gen Intel
        if (strpos($specs, 'lga1200') !== false || 
            strpos($specs, 'lga 1200') !== false) {
            $compatible = true;
            $reason = "Socket LGA1200 matches";
        }
        // Check for 500 series chipsets (compatible with 11th gen)
        else if (strpos($specs, 'z590') !== false || 
                 strpos($specs, 'b560') !== false || 
                 strpos($specs, 'h570') !== false || 
                 strpos($specs, 'h510') !== false) {
            $compatible = true;
            $reason = "500 series chipset compatible with 11th gen Intel";
        }
        // Check for 400 series chipsets (may need BIOS update for 11th gen)
        else if (strpos($specs, 'z490') !== false || 
                 strpos($specs, 'b460') !== false || 
                 strpos($specs, 'h470') !== false || 
                 strpos($specs, 'h410') !== false) {
            $compatible = true;
            $reason = "400 series chipset compatible with 11th gen Intel (may need BIOS update)";
        }
        else {
            $reason = "Not compatible with LGA1200/11th gen Intel";
        }
        
        echo "<tr>";
        echo "<td>" . htmlspecialchars($mb['Name']) . "</td>";
        echo "<td>" . htmlspecialchars($mb['Specs']) . "</td>";
        echo "<td>" . ($compatible ? "✓ Yes" : "✗ No") . "</td>";
        echo "<td>" . $reason . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} catch(PDOException $e) {
    echo "Error getting motherboards: " . $e->getMessage();
}

// Test the JavaScript filtering logic
echo "<h2>Simulating JavaScript Filtering Logic:</h2>";
echo "<p>This simulates how the JavaScript code would filter motherboards for Intel i5 11400</p>";

try {
    $stmt = $db->prepare("SELECT Name, Specs FROM Inventory WHERE Category = 'Motherboard' AND Stock_QTY > 0");
    $stmt->execute();
    $allMotherboards = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Motherboard Name</th><th>Specs</th><th>Would be shown in dropdown?</th><th>Matching Logic</th></tr>";
    
    foreach ($allMotherboards as $mb) {
        $specs = strtolower($mb['Specs']);
        $name = strtolower($mb['Name']);
        $socketType = "LGA1200"; // For i5 11400
        $lga = str_replace('lga', '', strtolower($socketType));
        $lga = trim($lga);
        
        $wouldBeShown = false;
        $matchingLogic = "";
        
        // Simulate the JavaScript filtering logic
        if (strpos($specs, 'lga') !== false && 
            (strpos($specs, "lga$lga") !== false || 
             strpos($specs, "lga $lga") !== false ||
             // Also check for chipset compatibility
             ($lga === '1200' && (
                 strpos($specs, 'z590') !== false || 
                 strpos($specs, 'b560') !== false || 
                 strpos($specs, 'h510') !== false ||
                 strpos($specs, 'z490') !== false || 
                 strpos($specs, 'b460') !== false || 
                 strpos($specs, 'h410') !== false
             ))
            )) {
            $wouldBeShown = true;
            $matchingLogic = "Matched by socket type or chipset";
        }
        
        echo "<tr>";
        echo "<td>" . htmlspecialchars($mb['Name']) . "</td>";
        echo "<td>" . htmlspecialchars($mb['Specs']) . "</td>";
        echo "<td>" . ($wouldBeShown ? "✓ Yes" : "✗ No") . "</td>";
        echo "<td>" . $matchingLogic . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} catch(PDOException $e) {
    echo "Error in simulation: " . $e->getMessage();
}
?>
