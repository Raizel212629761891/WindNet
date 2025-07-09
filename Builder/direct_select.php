<?php
// Direct component selection tool - guaranteed to work

// Database connection
try {
    $db = new PDO('sqlite:inventory.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo '<div style="color: red; font-weight: bold;">Database connection error: ' . $e->getMessage() . '</div>';
    exit;
}

// Get components for each category
$categories = [
    'Monitor' => 'Monitor',
    'Hard Drive' => 'Storage' // Hard Drive uses Storage category in the database
];

$components = [];
foreach ($categories as $displayCategory => $dbCategory) {
    try {
        if ($displayCategory === 'Hard Drive') {
            // Special handling for Hard Drive (filter Storage category)
            $stmt = $db->prepare("SELECT * FROM Inventory WHERE Category = :cat AND Stock_QTY > 0");
            $stmt->bindParam(':cat', $dbCategory);
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
            
            $components[$displayCategory] = $filteredItems;
        } else {
            // Standard category handling
            $stmt = $db->prepare("SELECT * FROM Inventory WHERE Category = :cat AND Stock_QTY > 0");
            $stmt->bindParam(':cat', $dbCategory);
            $stmt->execute();
            $components[$displayCategory] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch(PDOException $e) {
        echo '<div style="color: red; font-weight: bold;">Database query error for ' . $displayCategory . ': ' . $e->getMessage() . '</div>';
        $components[$displayCategory] = [];
    }
}

// Handle form submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'select') {
    $category = isset($_POST['category']) ? $_POST['category'] : '';
    $component = isset($_POST['component']) ? $_POST['component'] : '';
    
    if (!empty($category) && !empty($component)) {
        $message = "Selected $category: $component";
        
        // Store the selection in a cookie for the PC Builder page to use
        setcookie("selected_{$category}", $component, time() + 3600, '/');
    }
}

// Function to fix image path
function fixImagePath($path) {
    if (empty($path)) {
        return "assets/images/components/default.png";
    }
    
    // If the path starts with "assets/" without "../", it's already correct
    return $path;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Direct Component Selection</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/lucide@latest/dist/umd/lucide.min.js"></script>
</head>
<body class="bg-gray-900 text-white">
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Direct Component Selection</h1>
            <a href="includes/pc-builder1.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                Back to PC Builder
            </a>
        </div>
        
        <?php if (!empty($message)): ?>
            <div class="bg-green-600 text-white p-4 rounded-lg mb-6">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <?php foreach ($categories as $displayCategory => $dbCategory): ?>
                <div class="bg-gray-800 rounded-lg overflow-hidden border border-gray-700">
                    <div class="bg-gray-700 p-4">
                        <h2 class="text-xl font-bold flex items-center">
                            <i data-lucide="<?php echo $displayCategory === 'Monitor' ? 'monitor' : 'hard-drive'; ?>" class="w-5 h-5 mr-2 text-blue-400"></i>
                            <?php echo htmlspecialchars($displayCategory); ?> Components
                        </h2>
                    </div>
                    
                    <div class="p-4">
                        <?php if (empty($components[$displayCategory])): ?>
                            <p class="text-gray-400">No components found for this category.</p>
                        <?php else: ?>
                            <form method="post" action="">
                                <input type="hidden" name="action" value="select">
                                <input type="hidden" name="category" value="<?php echo htmlspecialchars($displayCategory); ?>">
                                
                                <div class="mb-4">
                                    <label class="block text-gray-400 mb-2">Select a component:</label>
                                    <select name="component" class="w-full bg-gray-900 border border-gray-700 rounded-lg p-2">
                                        <option value="">-- Select <?php echo htmlspecialchars($displayCategory); ?> --</option>
                                        <?php foreach ($components[$displayCategory] as $component): ?>
                                            <option value="<?php echo htmlspecialchars($component['Name']); ?>">
                                                <?php echo htmlspecialchars($component['Name']); ?> - ₱<?php echo number_format($component['Price'], 2); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg w-full">
                                    Select <?php echo htmlspecialchars($displayCategory); ?>
                                </button>
                            </form>
                            
                            <div class="mt-6">
                                <h3 class="text-lg font-medium mb-3">Available Components:</h3>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <?php foreach ($components[$displayCategory] as $component): ?>
                                        <?php 
                                        // Fix image path
                                        $imagePath = fixImagePath($component['Image']); 
                                        ?>
                                        <div class="bg-gray-900 rounded-lg p-3 border border-gray-700">
                                            <div class="h-24 flex items-center justify-center mb-2 bg-gray-800 rounded-lg overflow-hidden">
                                                <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="<?php echo htmlspecialchars($component['Name']); ?>" class="max-h-full max-w-full object-contain">
                                            </div>
                                            <h4 class="text-sm font-medium truncate" title="<?php echo htmlspecialchars($component['Name']); ?>"><?php echo htmlspecialchars($component['Name']); ?></h4>
                                            <div class="text-blue-400 font-medium mt-1">₱<?php echo number_format($component['Price'], 2); ?></div>
                                            <form method="post" action="" class="mt-2">
                                                <input type="hidden" name="action" value="select">
                                                <input type="hidden" name="category" value="<?php echo htmlspecialchars($displayCategory); ?>">
                                                <input type="hidden" name="component" value="<?php echo htmlspecialchars($component['Name']); ?>">
                                                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-xs w-full">
                                                    Select This
                                                </button>
                                            </form>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="mt-8 bg-gray-800 rounded-lg p-4 border border-gray-700">
            <h2 class="text-xl font-bold mb-4">Instructions</h2>
            <ol class="list-decimal pl-5 space-y-2">
                <li>Select a Monitor or Hard Drive component from the lists above</li>
                <li>Click the "Select" button to confirm your selection</li>
                <li>Return to the PC Builder page using the "Back to PC Builder" button</li>
                <li>Your selection will be automatically applied</li>
            </ol>
        </div>
    </div>
    
    <script>
        // Initialize Lucide icons
        lucide.createIcons();
    </script>
</body>
</html>
