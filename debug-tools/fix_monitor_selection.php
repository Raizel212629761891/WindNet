<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Get the absolute path to the database
$dbPath = __DIR__ . '/includes/inventory.db';

// Check if database file exists
if (!file_exists($dbPath)) {
    echo "Database file not found at: " . $dbPath . "<br>";
    exit;
}

// Database connection
try {
    $db = new PDO('sqlite:' . $dbPath);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>Database Connection: Success</h2>";
    
    // Check for Monitor category in database
    $stmt = $db->prepare("SELECT COUNT(*) FROM Inventory WHERE Category = ?");
    $stmt->execute(['Monitor']);
    $monitorCount = $stmt->fetchColumn();
    
    echo "<p>Monitor items in database: " . $monitorCount . "</p>";
    
    // If no monitor items exist, add some sample ones
    if ($monitorCount == 0) {
        echo "<h3>Adding sample monitor items to database...</h3>";
        
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
        
        echo "<p>Sample monitor items added successfully!</p>";
    }
    
    // Check for CPU Cooler category in database
    $stmt = $db->prepare("SELECT COUNT(*) FROM Inventory WHERE Category = ?");
    $stmt->execute(['CPU Cooler']);
    $coolerCount = $stmt->fetchColumn();
    
    echo "<p>CPU Cooler items in database: " . $coolerCount . "</p>";
    
    // If no CPU Cooler items exist, add some sample ones
    if ($coolerCount == 0) {
        echo "<h3>Adding sample CPU Cooler items to database...</h3>";
        
        $coolers = [
            [
                'Name' => 'NZXT Kraken X63 280mm AIO',
                'Category' => 'CPU Cooler',
                'Cash_Price' => 7999,
                'Regular_Price' => 8999,
                'Stock_QTY' => 4,
                'Specs' => '280mm Radiator, RGB, Compatible with Intel & AMD',
                'Image' => '../assets/images/components/cpu_cooler/nzxt_kraken.jpg'
            ],
            [
                'Name' => 'Cooler Master Hyper 212 RGB',
                'Category' => 'CPU Cooler',
                'Cash_Price' => 2499,
                'Regular_Price' => 2999,
                'Stock_QTY' => 10,
                'Specs' => 'Air Cooler, 4 Heat Pipes, 120mm RGB Fan',
                'Image' => '../assets/images/components/cpu_cooler/hyper212.jpg'
            ],
            [
                'Name' => 'Corsair H100i RGB PRO XT 240mm',
                'Category' => 'CPU Cooler',
                'Cash_Price' => 6499,
                'Regular_Price' => 7499,
                'Stock_QTY' => 6,
                'Specs' => '240mm Radiator, RGB, Compatible with Intel & AMD',
                'Image' => '../assets/images/components/cpu_cooler/corsair_h100i.jpg'
            ]
        ];
        
        // Insert sample CPU Coolers
        $stmt = $db->prepare("INSERT INTO Inventory (Name, Category, Cash_Price, Regular_Price, Stock_QTY, Specs, Image) VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        foreach ($coolers as $cooler) {
            try {
                $stmt->execute([
                    $cooler['Name'],
                    $cooler['Category'],
                    $cooler['Cash_Price'],
                    $cooler['Regular_Price'],
                    $cooler['Stock_QTY'],
                    $cooler['Specs'],
                    $cooler['Image']
                ]);
                echo "<p>Added: " . $cooler['Name'] . "</p>";
            } catch (PDOException $e) {
                echo "<p>Error adding " . $cooler['Name'] . ": " . $e->getMessage() . "</p>";
            }
        }
        
        echo "<p>Sample CPU Cooler items added successfully!</p>";
    }
    
    echo "<h2>Fix Applied!</h2>";
    echo "<p>The monitor and CPU cooler components should now work correctly in the PC Builder.</p>";
    echo "<p>Please refresh the PC Builder page and try again.</p>";
    
} catch(PDOException $e) {
    echo "Database error: " . $e->getMessage();
}
?>
