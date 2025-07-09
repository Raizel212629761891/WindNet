<?php
// Database connection
try {
    $db = new PDO('sqlite:inventory.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo '<div style="color: red; font-weight: bold;">Database connection error: ' . $e->getMessage() . '</div>';
    exit;
}

// Get items from database based on category
function getItems($db, $category) {
    try {
        // Special handling for storage categories
        if ($category === 'Primary SSD' || $category === 'Secondary SSD' || $category === 'Hard Drive') {
            // Get the base category (Storage) and filter type
            $baseCategory = 'Storage';
            $filterType = '';
            
            // Determine filter based on category
            if ($category === 'Primary SSD' || $category === 'Secondary SSD') {
                $filterType = 'ssd';
            } else if ($category === 'Hard Drive') {
                $filterType = 'hdd';
            }
            
            // Get all storage items
            $stmt = $db->prepare("SELECT * FROM Inventory WHERE Category = :cat AND Stock_QTY > 0");
            $stmt->bindParam(':cat', $baseCategory);
            $stmt->execute();
            $allItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Filter items based on name
            $filteredItems = [];
            foreach ($allItems as $item) {
                $itemName = strtolower($item['Name']);
                
                if ($filterType === 'ssd' && (strpos($itemName, 'ssd') !== false || strpos($itemName, 'nvme') !== false)) {
                    // Both Primary SSD and Secondary SSD should show all SSD components
                    $filteredItems[] = $item;
                } else if ($filterType === 'hdd' && strpos($itemName, 'hdd') !== false) {
                    $filteredItems[] = $item;
                }
            }
            
            return $filteredItems;
        } else {
            // Standard category handling
            $stmt = $db->prepare("SELECT * FROM Inventory WHERE Category = :cat AND Stock_QTY > 0");
            $stmt->bindParam(':cat', $category);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch(PDOException $e) {
        // Return empty array if query fails
        return [];
    }
}

// Get categories to test
$categories = [
    'Monitor' => 'Monitor',
    'Hard Drive' => 'Hard Drive',
    'Processor' => 'Processor', // For comparison (working category)
    'Motherboard' => 'Motherboard' // For comparison (working category)
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Component Selection Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f5f7fa;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1, h2 {
            color: #0284c7;
        }
        .category-section {
            margin-bottom: 30px;
            padding: 15px;
            background-color: #f8fafc;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }
        .component-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        .component-item {
            background-color: #1e293b;
            color: white;
            border-radius: 8px;
            padding: 15px;
            cursor: pointer;
            transition: all 0.2s ease;
            border: 1px solid #334155;
        }
        .component-item:hover {
            transform: translateY(-2px);
            border-color: #0284c7;
            box-shadow: 0 5px 15px rgba(2, 132, 199, 0.2);
        }
        .component-image {
            height: 120px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 10px;
            background-color: #0f172a;
            border-radius: 4px;
            overflow: hidden;
        }
        .component-image img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        .component-name {
            font-weight: bold;
            margin-bottom: 5px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .component-price {
            color: #38bdf8;
            font-weight: bold;
        }
        .result-box {
            margin-top: 20px;
            padding: 15px;
            background-color: #f1f5f9;
            border-radius: 8px;
            border-left: 4px solid #0284c7;
        }
        .success {
            color: #16a34a;
            font-weight: bold;
        }
        .error {
            color: #dc2626;
            font-weight: bold;
        }
        .btn {
            background-color: #0284c7;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            margin-top: 10px;
        }
        .btn:hover {
            background-color: #0369a1;
        }
        .debug-info {
            margin-top: 20px;
            padding: 15px;
            background-color: #f1f5f9;
            border-radius: 8px;
            font-family: monospace;
            white-space: pre-wrap;
            max-height: 200px;
            overflow-y: auto;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Component Selection Test</h1>
        <p>This page tests component selection for problematic categories (Monitor and Hard Drive) compared to working categories.</p>
        
        <div id="result" class="result-box">
            <p>Select a component from any category to test selection.</p>
        </div>
        
        <?php foreach ($categories as $dbCategory => $displayCategory): ?>
            <div class="category-section">
                <h2><?= $displayCategory ?> Components</h2>
                <div class="component-grid">
                    <?php 
                    $items = getItems($db, $dbCategory);
                    if (empty($items)): 
                    ?>
                        <p>No components found for this category.</p>
                    <?php else: ?>
                        <?php foreach ($items as $item): ?>
                            <?php
                            // Get image path or use default placeholder
                            $imagePath = isset($item['Image']) && !empty($item['Image']) 
                                ? htmlspecialchars($item['Image']) 
                                : "../assets/images/components/" . strtolower($dbCategory) . "/default.png";
                                
                            // If the path starts with "assets/" without "../", add "../" prefix
                            if (strpos($imagePath, 'assets/') === 0) {
                                $imagePath = '../' . $imagePath;
                            }
                            ?>
                            <div class="component-item" onclick="selectComponent('<?= $dbCategory ?>', '<?= htmlspecialchars($item['Name']) ?>')">
                                <div class="component-image">
                                    <img src="<?= $imagePath ?>" alt="<?= htmlspecialchars($item['Name']) ?>">
                                </div>
                                <div class="component-name" title="<?= htmlspecialchars($item['Name']) ?>"><?= htmlspecialchars($item['Name']) ?></div>
                                <div class="component-price">₱<?= number_format($item['Price'], 2) ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
        
        <div id="debug" class="debug-info"></div>
        
        <a href="includes/pc-builder1.php" class="btn">Go to PC Builder</a>
    </div>
    
    <script>
        // Debug log function
        function log(message) {
            const debug = document.getElementById('debug');
            const timestamp = new Date().toLocaleTimeString();
            debug.innerHTML += `[${timestamp}] ${message}\n`;
            console.log(message);
        }
        
        // Component selection function
        function selectComponent(category, name) {
            log(`Selecting component: category=${category}, name=${name}`);
            
            const result = document.getElementById('result');
            result.innerHTML = `<p>Selected: <strong>${name}</strong> from category <strong>${category}</strong></p>`;
            
            // Create a test selection in localStorage to verify it works
            const selection = {
                category: category,
                name: name,
                timestamp: new Date().toISOString()
            };
            
            localStorage.setItem('test_selection', JSON.stringify(selection));
            log(`Saved selection to localStorage: ${JSON.stringify(selection)}`);
            
            // Create a function to test selection in the PC Builder
            const testFunction = `
                function testSelectInPCBuilder() {
                    // This function will be copied to the PC Builder page
                    console.log("Testing component selection");
                    
                    // Get the selection from localStorage
                    const selection = JSON.parse(localStorage.getItem('test_selection'));
                    if (!selection) {
                        console.error("No selection found in localStorage");
                        return;
                    }
                    
                    console.log("Found selection:", selection);
                    
                    // Try to select the component
                    try {
                        // First try with directComponentSelect if available
                        if (typeof directComponentSelect === 'function') {
                            console.log("Using directComponentSelect");
                            directComponentSelect(selection.category, selection.name);
                        }
                        // Then try with forceSelectComponent
                        else if (typeof forceSelectComponent === 'function') {
                            console.log("Using forceSelectComponent");
                            forceSelectComponent(selection.category, selection.name);
                        }
                        // Finally try with selectComponent
                        else if (typeof selectComponent === 'function') {
                            console.log("Using selectComponent");
                            selectComponent(selection.category, selection.name);
                        }
                        else {
                            console.error("No selection function found");
                        }
                    } catch (error) {
                        console.error("Error selecting component:", error);
                    }
                }
                
                // Run the test
                testSelectInPCBuilder();
            `;
            
            // Add a button to copy the test function
            result.innerHTML += `
                <p>To test this selection in the PC Builder:</p>
                <ol>
                    <li>Click the button below to copy the test function</li>
                    <li>Go to the PC Builder page</li>
                    <li>Open the browser console (F12 or right-click > Inspect > Console)</li>
                    <li>Paste and run the function</li>
                </ol>
                <button onclick="copyTestFunction()" class="btn">Copy Test Function</button>
            `;
            
            // Add the test function to the window object
            window.testFunction = testFunction;
        }
        
        // Function to copy the test function to clipboard
        function copyTestFunction() {
            if (!navigator.clipboard) {
                log("Clipboard API not available");
                return;
            }
            
            navigator.clipboard.writeText(window.testFunction)
                .then(() => {
                    log("Test function copied to clipboard");
                    alert("Test function copied to clipboard. Go to the PC Builder page, open the console, and paste it.");
                })
                .catch(err => {
                    log(`Error copying to clipboard: ${err}`);
                    alert("Failed to copy to clipboard. Please check the console for the test function.");
                    console.log("Test Function:", window.testFunction);
                });
        }
    </script>
</body>
</html>
