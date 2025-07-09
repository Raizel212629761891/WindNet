<?php
// Functions for PC Builder

// Define categories with their icons
function getCategories() {
    return [
        'Processor' => ['label' => 'CPU', 'icon' => 'cpu'],
        'Motherboard' => ['label' => 'Motherboard', 'icon' => 'motherboard'],
        'RAM' => ['label' => 'Memory', 'icon' => 'memory-stick'],
        'Storage' => ['label' => 'Storage', 'icon' => 'hard-drive'],
        'GPU' => ['label' => 'Graphics Card', 'icon' => 'gpu'],
        'Power Supply' => ['label' => 'Power Supply', 'icon' => 'battery-charging'],
        'Casing' => ['label' => 'Case', 'icon' => 'box']
    ];
}

// Get items from database based on category
function getItems($db, $category) {
    try {
        // Special handling for storage categories
        if ($category === 'Primary SSD' || $category === 'Secondary SSD') {
            // For SSD categories, get items from Storage category and filter for SSD
            $baseCategory = 'Storage';
            $stmt = $db->prepare("SELECT * FROM Inventory WHERE Category = :cat AND Status = 'In Stock'");
            $stmt->bindParam(':cat', $baseCategory);
            $stmt->execute();
            $allItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Filter items based on name containing SSD or NVME
            $filteredItems = [];
            foreach ($allItems as $item) {
                $itemName = strtolower($item['Name']);
                if (strpos($itemName, 'ssd') !== false || strpos($itemName, 'nvme') !== false) {
                    $filteredItems[] = $item;
                }
            }
            return $filteredItems;
        } 
        else if ($category === 'Hard Drive') {
            // For Hard Drive category, get items from Storage category and filter for HDD
            $baseCategory = 'Storage';
            $stmt = $db->prepare("SELECT * FROM Inventory WHERE Category = :cat AND Status = 'In Stock'");
            $stmt->bindParam(':cat', $baseCategory);
            $stmt->execute();
            $allItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Filter items based on name containing HDD or not containing SSD/NVME
            $filteredItems = [];
            foreach ($allItems as $item) {
                $itemName = strtolower($item['Name']);
                if (strpos($itemName, 'hdd') !== false || 
                    (strpos($itemName, 'ssd') === false && strpos($itemName, 'nvme') === false)) {
                    $filteredItems[] = $item;
                }
            }
            return $filteredItems;
        }
        else {
            // Standard query for other categories
            $stmt = $db->prepare("SELECT * FROM Inventory WHERE Category = :cat AND Status = 'In Stock'");
            $stmt->bindParam(':cat', $category);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch(PDOException $e) {
        echo "<div class='bg-red-500 p-4 mb-4 rounded-lg text-white'>Error fetching items: " . $e->getMessage() . "</div>";
        return [];
    }
}
?>