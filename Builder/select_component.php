<?php
// Database connection
try {
    $db = new PDO('sqlite:inventory.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo '<div style="color: red; font-weight: bold;">Database connection error: ' . $e->getMessage() . '</div>';
    exit;
}

// Get category from URL
$category = isset($_GET['category']) ? $_GET['category'] : '';
$returnUrl = isset($_GET['return']) ? $_GET['return'] : 'includes/pc-builder1.php';

// Validate category
$validCategories = [
    'Monitor', 'Hard Drive', 'CPU', 'Processor', 'Motherboard', 'GPU', 'RAM', 'Memory', 'Power Supply', 'Casing', 'Case', 'Primary SSD', 'Secondary SSD', 'SSD', 'HDD', 'Fan', 'Keyboard', 'Mouse', 'Headset', 'Speaker', 'Mousepad', 'Mic', 'Networking', 'Cable Adapters', 'Power Devices', 'Other'
];
if (!in_array($category, $validCategories)) {
    echo '<div style="color: red; font-weight: bold;">Invalid category</div>';
    exit;
}

// Get items from database based on category
function getItems($db, $category) {
    try {
        // Special handling for Hard Drive
        if ($category === 'Hard Drive') {
            // Get all storage items
            $stmt = $db->prepare("SELECT * FROM Inventory WHERE Category = 'Storage' AND Stock_QTY > 0");
            $stmt->execute();
            $allItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Filter for HDD only
            $filteredItems = [];
            foreach ($allItems as $item) {
                $itemName = strtolower($item['Name']);
                if (strpos($itemName, 'hdd') !== false) {
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
        echo '<div style="color: red; font-weight: bold;">Database query error: ' . $e->getMessage() . '</div>';
        return [];
    }
}

// Get items for the selected category
$items = getItems($db, $category);

// Function to fix image path
function fixImagePath($path) {
    if (empty($path)) {
        return "../assets/images/components/default.png";
    }
    
    // If the path starts with "assets/" without "../", add "../" prefix
    if (strpos($path, 'assets/') === 0) {
        return '../' . $path;
    }
    
    return $path;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select <?php echo htmlspecialchars($category); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/lucide@latest/dist/umd/lucide.min.js"></script>
    <style>
        body {
            background-color: #0f172a;
            color: white;
            font-family: 'Inter', sans-serif;
        }
        .component-card {
            transition: all 0.2s ease;
            cursor: pointer;
        }
        .component-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.5);
            border-color: #3b82f6;
        }
        /* Modal-specific styles */
        .modal-component-card {
            background: #181f2a;
            border-radius: 14px;
            box-shadow: 0 4px 24px rgba(56,189,248,0.10);
            border: 1.5px solid #232b3b;
            padding: 18px 12px 14px 12px;
            display: flex;
            flex-direction: column;
            align-items: center;
            transition: box-shadow 0.2s, border 0.2s;
            cursor: pointer;
            min-height: 320px;
        }
        .modal-component-card:hover {
            box-shadow: 0 8px 32px rgba(56,189,248,0.18);
            border-color: #38bdf8;
        }
        .modal-image-container {
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 12px;
            min-height: 140px;
        }
        .modal-component-image {
            width: 80%;
            height: 120px;
            object-fit: contain;
            border-radius: 10px;
            background: #232b3b;
            box-shadow: 0 2px 8px rgba(56,189,248,0.10);
            border: 1.5px solid #232b3b;
            display: block;
        }
        .modal-component-details {
            width: 100%;
            text-align: center;
        }
        .modal-component-name {
            font-weight: 600;
            font-size: 1rem;
            color: #fff;
            margin-bottom: 6px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .modal-component-prices {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 6px;
        }
        .modal-srp {
            color: #94a3b8;
            font-size: 0.95rem;
        }
        .modal-srp-strike {
            text-decoration: line-through;
            color: #64748b;
        }
        .modal-price {
            color: #38bdf8;
            font-size: 1.15rem;
            font-weight: 700;
            margin-top: 2px;
        }
        .modal-stock {
            font-size: 0.95rem;
            margin-top: 4px;
            font-weight: 500;
        }
        .modal-stock.in-stock {
            color: #22c55e;
        }
        .modal-stock.out-stock {
            color: #ef4444;
        }
    </style>
</head>
<body class="min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold flex items-center">
                <i data-lucide="<?php echo $category === 'Monitor' ? 'monitor' : 'hard-drive'; ?>" class="w-6 h-6 mr-2 text-blue-500"></i>
                Select <?php echo htmlspecialchars($category); ?>
            </h1>
            <a href="<?php echo htmlspecialchars($returnUrl); ?>" class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg flex items-center">
                <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
                Back to PC Builder
            </a>
        </div>
        
        <?php if (empty($items)): ?>
            <div class="bg-gray-800 rounded-lg p-8 text-center">
                <i data-lucide="package-x" class="w-16 h-16 mx-auto text-gray-500 mb-4"></i>
                <p class="text-gray-400">No <?php echo htmlspecialchars($category); ?> components found</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <?php foreach ($items as $item): ?>
                    <div class="component-card modal-component-card" onclick="selectComponent('<?php echo htmlspecialchars($item['Name']); ?>')">
                        <div class="modal-image-container">
                            <img src="<?php echo fixImagePath($item['Image']); ?>"
                                 alt="<?php echo htmlspecialchars($item['Name']); ?>"
                                 class="modal-component-image"
                            >
                        </div>
                        <div class="modal-component-details">
                            <h3 class="modal-component-name" title="<?php echo htmlspecialchars($item['Name']); ?>">
                                <?php echo htmlspecialchars($item['Name']); ?>
                            </h3>
                            <div class="modal-component-prices">
                                <span class="modal-srp">SRP: <span class="modal-srp-strike">₱<?php echo number_format($item['SRP'], 2); ?></span></span>
                                <span class="modal-price">₱<?php echo number_format($item['Price'], 2); ?></span>
                            </div>
                            <div class="modal-stock <?php echo $item['Stock_QTY'] > 0 ? 'in-stock' : 'out-stock'; ?>">
                                <?php echo $item['Stock_QTY'] > 0 ? 'In Stock' : 'Out of Stock'; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        // Initialize Lucide icons
        lucide.createIcons();
        
        // Function to select a component and return to PC Builder
        function selectComponent(name) {
            console.log(`Selected ${name}`);
            
            // Store the selection in localStorage
            localStorage.setItem('selected_component', JSON.stringify({
                category: '<?php echo $category; ?>',
                name: name,
                timestamp: new Date().toISOString()
            }));
            
            // Redirect back to PC Builder
            window.location.href = '<?php echo $returnUrl; ?>';
        }
    </script>
</body>
</html>
