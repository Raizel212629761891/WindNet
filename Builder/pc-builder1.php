<?php
// Include database connection file
require_once 'db_connect.php';

// Get database connection
try {
    $db = getDbConnection();
} catch(PDOException $e) {
    echo '<div class="bg-red-500 p-4 mb-4 rounded-lg text-white">Database connection error: ' . $e->getMessage() . '</div>';
}

// Define categories with their icons (using Lucide icon names)
$categories = [
    // Core Components
    'Processor' => ['label' => 'CPU', 'icon' => 'cpu'],
    'Motherboard' => ['label' => 'Motherboard', 'icon' => 'circuit-board'],
    'RAM' => ['label' => 'Memory', 'icon' => 'memory-stick'],
    'GPU' => ['label' => 'Graphics Card', 'icon' => 'gpu'],
    'CPU Cooler' => ['label' => 'CPU Cooler', 'icon' => 'fan'],
    'Power Supply' => ['label' => 'Power Supply', 'icon' => 'battery-charging'],
    'Casing' => ['label' => 'Case', 'icon' => 'box'],
    'Monitor' => ['label' => 'Monitor', 'icon' => 'monitor'],
    
    // Storage Options
    'Primary SSD' => ['label' => 'Primary SSD', 'icon' => 'hard-drive', 'filter' => 'ssd'],
    'Secondary SSD' => ['label' => 'Secondary SSD', 'icon' => 'hard-drive', 'filter' => 'ssd'],
    'Hard Drive' => ['label' => 'Hard Drive', 'icon' => 'database', 'filter' => 'hdd'],
    
    // Cooling Options
    'Fan' => ['label' => 'Fan', 'icon' => 'fan'],
    'Optional Fan' => ['label' => 'Optional Fan', 'icon' => 'fan'],
    
    // Peripherals
    'Keyboard' => ['label' => 'Keyboard', 'icon' => 'keyboard'],
    'Mouse' => ['label' => 'Mouse', 'icon' => 'mouse-pointer'],
    'Headset' => ['label' => 'Headset', 'icon' => 'headphones'],
    'Speaker' => ['label' => 'Speaker', 'icon' => 'volume-2'],
    'Mic' => ['label' => 'Mic', 'icon' => 'mic'],
    
    // Other Components
    'Power Devices' => ['label' => 'Power Devices', 'icon' => 'zap'],
    'Other' => ['label' => 'Other', 'icon' => 'plus-circle']
];

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
        } 
        // Special handling for Optional Fan - use the same items as Fan
        else if ($category === 'Optional Fan') {
            $fanCategory = 'Fan';
            $stmt = $db->prepare("SELECT * FROM Inventory WHERE Category = :cat AND Stock_QTY > 0");
            $stmt->bindParam(':cat', $fanCategory);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        else {
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PC Builder - Wind Net</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/lucide@latest/dist/umd/lucide.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="Css/pc-builder-custom.css">
    <script src="../assets/js/image-path-fix.js"></script>
    <script src="../Js/price_sync.js"></script>
    <script src="../Js/standalone_selector.js"></script>
    <script src="../Js/pc-builder.js"></script>
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            200: '#bae6fd',
                            300: '#7dd3fc',
                            400: '#38bdf8',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                            800: '#075985',
                            900: '#0c4a6e',
                        },
                        secondary: {
                            50: '#f5f3ff',
                            500: '#8b5cf6',
                            700: '#6d28d9',
                            900: '#4c1d95',
                        },
                        accent: {
                            400: '#fb7185',
                            500: '#f43f5e',
                            600: '#e11d48',
                        }
                    }
                }
            }
        }
    </script>
   
</head>
<body class="bg-gray-900 text-white font-sans flex flex-col min-h-screen">
    <!-- Animated Gradient Background -->
    <div class="fixed inset-0 bg-gradient-to-br from-primary-700 via-secondary-700 to-gray-900 opacity-50 z-0"></div>

    <?php include 'navbar.php'; ?>

    <!-- Main Content -->
    <div class="relative z-10 w-full max-w-screen-2xl mx-auto p-6 pt-8 flex-1">
        <div class="text-center mb-10 animate__animated animate__fadeIn">
            <h1 class="text-4xl md:text-5xl font-bold mb-2 bg-clip-text text-transparent bg-gradient-to-r from-primary-400 to-secondary-500">Build Your Dream PC</h1>
            <p class="text-gray-300 max-w-3xl mx-auto">Select your components below to create your perfect custom build. We'll help you create a powerful machine that meets your needs.</p>
        </div>
        <div class="flex flex-col lg:flex-row gap-6">
            <div class="flex-1 min-w-0">
                <?php include 'pc-builder-main-container.php'; ?>
                <?php include 'build-summary.php'; ?>
            </div>
            <div class="md:w-1/4 bg-gray-900 p-6 border-l border-gray-700">
                <?php include 'installment-options.php'; ?>
            </div>
        </div>
        <?php include 'compatibility-guide.php'; ?>
    </div>

    <?php include 'component-selector-modal.php'; ?>
    

    
    <!-- JavaScript -->
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <!-- Price calculation script -->

    <script src="../Js/price_calculator.js"></script>

    <script src="../Js/component_utils.js"></script>

    <!-- Simple image path fix script -->
    <script>
    // Wait for page to load
    window.addEventListener('load', function() {
        // Fix image paths function
        function fixPaths() {
            console.log('Fixing image paths...');
            var images = document.querySelectorAll('img');
            var fixed = 0;
            
            images.forEach(function(img) {
                var src = img.getAttribute('src');
                if (src) {
                    var newSrc = src;
                    
                    // Fix monitor paths
                    if (src.indexOf('components/monitor/Monitor/') !== -1) {
                        newSrc = src.replace('components/monitor/Monitor/', 'components/monitor/');
                        fixed++;
                    }
                    
                    // Fix hard drive paths
                    if (src.indexOf('components/hard drive/Hard Drive/') !== -1) {
                        newSrc = src.replace('components/hard drive/Hard Drive/', 'components/hard drive/');
                        fixed++;
                    }
                    
                    // Fix CPU cooler paths
                    if (src.indexOf('components/cpu cooler/CPU Cooler/') !== -1) {
                        newSrc = src.replace('components/cpu cooler/CPU Cooler/', 'components/cpu cooler/');
                        fixed++;
                    }
                    
                    // Fix graphics card paths
                    if (src.indexOf('components/graphics card/Graphics Card/') !== -1) {
                        newSrc = src.replace('components/graphics card/Graphics Card/', 'components/graphics card/');
                        fixed++;
                    }
                    
                    // Fix backslashes in path
                    if (src.indexOf('\\') !== -1) {
                        newSrc = newSrc.replace(/\\/g, '/');
                        fixed++;
                    }
                    
                    // Update image if path changed
                    if (newSrc !== src) {
                        console.log('Fixed path: ' + src + ' → ' + newSrc);
                        img.setAttribute('src', newSrc);
                    }
                }
            });
            
            console.log('Fixed ' + fixed + ' image paths');
            return fixed;
        }
        
        // Run the fix immediately
        fixPaths();
        
        // Also fix after any select change
        document.querySelectorAll('select').forEach(function(select) {
            select.addEventListener('change', function() {
                setTimeout(fixPaths, 200);
            });
        });
    });
    </script>
</body>
</html>