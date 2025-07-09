<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Get the absolute path to the database
$dbPath = __DIR__ . '/includes/inventory.db';

// Check if database file exists
if (!file_exists($dbPath)) {
    echo "Database file not found at: " . $dbPath;
    exit;
}

// Database connection
try {
    $db = new PDO('sqlite:' . $dbPath);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check for Monitor category
    $stmt = $db->prepare("SELECT COUNT(*) FROM Inventory WHERE Category = ?");
    $stmt->execute(['Monitor']);
    $monitorCount = $stmt->fetchColumn();
    
    echo "<h2>Monitor Category Check:</h2>";
    echo "<p>Monitor items in database: " . $monitorCount . "</p>";
    
    // If there are monitor items, show them
    if ($monitorCount > 0) {
        $stmt = $db->prepare("SELECT * FROM Inventory WHERE Category = ? LIMIT 5");
        $stmt->execute(['Monitor']);
        $monitors = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>Sample Monitor Items:</h3>";
        echo "<pre>";
        print_r($monitors);
        echo "</pre>";
    } else {
        // Add some sample monitor items if none exist
        echo "<h3>Adding Sample Monitor Items:</h3>";
        
        $monitors = [
            [
                'Name' => 'ASUS TUF Gaming VG27AQ 27" 165Hz IPS',
                'Category' => 'Monitor',
                'Cash_Price' => 15999,
                'Regular_Price' => 17999,
                'Stock_QTY' => 5,
                'Specs' => '27-inch, 2560x1440, 165Hz, 1ms, IPS, G-Sync Compatible',
                'Image' => '../assets/images/components/monitor/asus_tuf.jpg'
            ],
            [
                'Name' => 'LG UltraGear 24GN600-B 24" 144Hz',
                'Category' => 'Monitor',
                'Cash_Price' => 9999,
                'Regular_Price' => 11999,
                'Stock_QTY' => 8,
                'Specs' => '24-inch, 1920x1080, 144Hz, 1ms, IPS, FreeSync',
                'Image' => '../assets/images/components/monitor/lg_ultragear.jpg'
            ],
            [
                'Name' => 'Samsung Odyssey G5 32" 144Hz Curved',
                'Category' => 'Monitor',
                'Cash_Price' => 18999,
                'Regular_Price' => 21999,
                'Stock_QTY' => 3,
                'Specs' => '32-inch, 2560x1440, 144Hz, 1ms, VA, 1000R Curved, FreeSync',
                'Image' => '../assets/images/components/monitor/samsung_odyssey.jpg'
            ]
        ];
        
        // Insert sample monitors
        $stmt = $db->prepare("INSERT INTO Inventory (Name, Category, Cash_Price, Regular_Price, Stock_QTY, Specs, Image) VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        foreach ($monitors as $monitor) {
            try {
                $stmt->execute([
                    $monitor['Name'],
                    $monitor['Category'],
                    $monitor['Cash_Price'],
                    $monitor['Regular_Price'],
                    $monitor['Stock_QTY'],
                    $monitor['Specs'],
                    $monitor['Image']
                ]);
                echo "<p>Added: " . $monitor['Name'] . "</p>";
            } catch (PDOException $e) {
                echo "<p>Error adding " . $monitor['Name'] . ": " . $e->getMessage() . "</p>";
            }
        }
    }
    
    // Now check the openComponentSelector function behavior for Monitor
    echo "<h2>Debugging Component Selection:</h2>";
    echo "<p>Check the JavaScript console for errors when clicking on the Monitor component.</p>";
    echo "<p>Make sure the category name in the database matches exactly what's expected in the JavaScript code.</p>";
    
} catch(PDOException $e) {
    echo "Database error: " . $e->getMessage();
}
?>
