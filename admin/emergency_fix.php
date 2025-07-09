<?php
// This is an emergency fix for the Monitor and Hard Drive selection issue

// Get the component type from the URL
$component = isset($_GET['component']) ? $_GET['component'] : '';
$name = isset($_GET['name']) ? $_GET['name'] : '';

// Validate component type
if ($component !== 'Monitor' && $component !== 'Hard Drive') {
    echo "Invalid component type. Must be 'Monitor' or 'Hard Drive'.";
    exit;
}

// If no name is provided, show the selection interface
if (empty($name)) {
    // Database connection
    try {
        $db = new PDO('sqlite:inventory.db');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch(PDOException $e) {
        echo "Database connection error: " . $e->getMessage();
        exit;
    }
    
    // Get components from database
    try {
        if ($component === 'Monitor') {
            $stmt = $db->prepare("SELECT * FROM Inventory WHERE Category = 'Monitor' AND Stock_QTY > 0");
            $stmt->execute();
        } else { // Hard Drive
            $stmt = $db->prepare("SELECT * FROM Inventory WHERE Category = 'Storage' AND Stock_QTY > 0");
            $stmt->execute();
            $allItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Filter for HDD only
            $items = [];
            foreach ($allItems as $item) {
                $itemName = strtolower($item['Name']);
                if (strpos($itemName, 'hdd') !== false) {
                    $items[] = $item;
                }
            }
        }
        
        if ($component === 'Monitor') {
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch(PDOException $e) {
        echo "Database query error: " . $e->getMessage();
        exit;
    }
    
    // Display the selection interface
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Select <?php echo htmlspecialchars($component); ?></title>
        <style>
            body {
                font-family: Arial, sans-serif;
                margin: 0;
                padding: 20px;
                background-color: #0f172a;
                color: white;
            }
            h1 {
                color: #3b82f6;
            }
            .component-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
                gap: 20px;
                margin-top: 20px;
            }
            .component-card {
                background-color: #1e293b;
                border: 1px solid #334155;
                border-radius: 8px;
                overflow: hidden;
                transition: all 0.3s ease;
                cursor: pointer;
            }
            .component-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.5);
                border-color: #3b82f6;
            }
            .component-image {
                height: 150px;
                background-color: #0f172a;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 10px;
            }
            .component-image img {
                max-width: 100%;
                max-height: 100%;
                object-fit: contain;
            }
            .component-details {
                padding: 15px;
            }
            .component-name {
                font-weight: bold;
                margin-bottom: 8px;
            }
            .component-price {
                color: #3b82f6;
                font-weight: bold;
                font-size: 18px;
            }
            .back-button {
                display: inline-block;
                background-color: #334155;
                color: white;
                padding: 10px 15px;
                border-radius: 5px;
                text-decoration: none;
                margin-bottom: 20px;
            }
            .back-button:hover {
                background-color: #475569;
            }
            .stock {
                font-size: 12px;
                color: #10b981;
                margin-top: 5px;
            }
        </style>
    </head>
    <body>
        <a href="includes/pc-builder1.php" class="back-button">← Back to PC Builder</a>
        
        <h1>Select <?php echo htmlspecialchars($component); ?></h1>
        
        <?php if (empty($items)): ?>
            <p>No <?php echo htmlspecialchars($component); ?> components found.</p>
        <?php else: ?>
            <div class="component-grid">
                <?php foreach ($items as $item): ?>
                    <?php 
                    // Fix image path
                    $imagePath = !empty($item['Image']) ? $item['Image'] : "assets/images/components/default.png";
                    // If the path starts with "assets/" without "../", add "../" prefix
                    if (strpos($imagePath, 'assets/') === 0) {
                        $imagePath = '../' . $imagePath;
                    }
                    ?>
                    <div class="component-card" onclick="selectComponent('<?php echo htmlspecialchars($item['Name']); ?>')">
                        <div class="component-image">
                            <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="<?php echo htmlspecialchars($item['Name']); ?>">
                        </div>
                        <div class="component-details">
                            <div class="component-name"><?php echo htmlspecialchars($item['Name']); ?></div>
                            <div class="component-price">₱<?php echo number_format($item['Price'], 2); ?></div>
                            <div class="stock">In Stock</div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <script>
            function selectComponent(name) {
                // Redirect to this same page with the selected component name
                window.location.href = 'emergency_fix.php?component=<?php echo urlencode($component); ?>&name=' + encodeURIComponent(name);
            }
        </script>
    </body>
    </html>
    <?php
    exit;
}

// If we get here, a component name was selected, so we'll apply it directly to the database
try {
    // Connect to the database
    $db = new PDO('sqlite:inventory.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get the current PC Build ID from the session or cookie
    $buildId = isset($_COOKIE['current_build_id']) ? $_COOKIE['current_build_id'] : null;
    
    if (!$buildId) {
        // Create a new build ID
        $buildId = uniqid('build_');
        setcookie('current_build_id', $buildId, time() + 86400, '/'); // 24 hours
    }
    
    // Check if this component already exists in the build
    $stmt = $db->prepare("SELECT * FROM PC_Builds WHERE BuildID = :buildId AND ComponentType = :componentType");
    $stmt->bindParam(':buildId', $buildId);
    $stmt->bindParam(':componentType', $component);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        // Update existing component
        $stmt = $db->prepare("UPDATE PC_Builds SET ComponentName = :componentName WHERE BuildID = :buildId AND ComponentType = :componentType");
    } else {
        // Insert new component
        $stmt = $db->prepare("INSERT INTO PC_Builds (BuildID, ComponentType, ComponentName) VALUES (:buildId, :componentType, :componentName)");
    }
    
    $stmt->bindParam(':buildId', $buildId);
    $stmt->bindParam(':componentType', $component);
    $stmt->bindParam(':componentName', $name);
    $stmt->execute();
    
    // Success message with redirect
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Component Selected</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                margin: 0;
                padding: 20px;
                background-color: #0f172a;
                color: white;
                text-align: center;
            }
            .success-message {
                background-color: #10b981;
                color: white;
                padding: 20px;
                border-radius: 8px;
                margin: 50px auto;
                max-width: 500px;
            }
            .redirect-message {
                margin-top: 20px;
                color: #94a3b8;
            }
        </style>
    </head>
    <body>
        <div class="success-message">
            <h2>✓ <?php echo htmlspecialchars($component); ?> Selected!</h2>
            <p>You selected: <?php echo htmlspecialchars($name); ?></p>
        </div>
        
        <div class="redirect-message">
            <p>Redirecting back to PC Builder...</p>
        </div>
        
        <script>
            // Redirect back to PC Builder after 2 seconds
            setTimeout(function() {
                window.location.href = 'includes/pc-builder1.php';
            }, 2000);
        </script>
    </body>
    </html>
    <?php
} catch(PDOException $e) {
    echo "Database error: " . $e->getMessage();
}
?>
