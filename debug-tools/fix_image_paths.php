<?php
// Database connection
try {
    $db = new PDO('sqlite:inventory.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Database connection error: " . $e->getMessage();
    exit;
}

// Function to update image paths in the database
function updateImagePath($db, $itemName, $newPath, $category = null) {
    try {
        if ($category) {
            $stmt = $db->prepare("UPDATE Inventory SET Image = :path WHERE Name LIKE :name AND Category = :category");
            $stmt->bindParam(':category', $category);
        } else {
            $stmt = $db->prepare("UPDATE Inventory SET Image = :path WHERE Name LIKE :name");
        }
        $stmt->bindParam(':path', $newPath);
        $nameLike = "%" . $itemName . "%";
        $stmt->bindParam(':name', $nameLike);
        $stmt->execute();
        return $stmt->rowCount();
    } catch(PDOException $e) {
        return "Error: " . $e->getMessage();
    }
}

// Function to fix paths with duplicate category folders
function fixDuplicateCategoryPaths($db, $category) {
    try {
        // Get all items in the category
        $stmt = $db->prepare("SELECT ID, Name, Image FROM Inventory WHERE Category = :category");
        $stmt->bindParam(':category', $category);
        $stmt->execute();
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $count = 0;
        $categoryLower = strtolower($category);
        
        foreach ($items as $item) {
            if (!empty($item['Image'])) {
                $imagePath = $item['Image'];
                
                // Fix double category in path
                $doublePathPattern = "components/{$categoryLower}/{$category}/";
                $correctPathPattern = "components/{$categoryLower}/";
                $newPath = str_replace($doublePathPattern, $correctPathPattern, $imagePath);
                
                // Replace backslashes with forward slashes
                $newPath = str_replace("\\", '/', $newPath);
                
                // Update if changed
                if ($newPath !== $imagePath) {
                    $updateStmt = $db->prepare("UPDATE Inventory SET Image = :path WHERE ID = :id");
                    $updateStmt->bindParam(':path', $newPath);
                    $updateStmt->bindParam(':id', $item['ID']);
                    $updateStmt->execute();
                    $count++;
                }
            }
        }
        
        return $count;
    } catch(PDOException $e) {
        return "Error: " . $e->getMessage();
    }
}

// Update specific processor image paths
$updates = [
    'Ryzen 5 5600G' => '../assets/images/components/processor/R5 5600g.jpg',
    'Ryzen 5 4650G' => '../assets/images/components/processor/Ryzen 5 4650g.jpg'
];

echo "<h1>Fixing Image Paths</h1>";
echo "<h2>1. Updating Specific Processor Paths</h2>";
echo "<ul>";

foreach ($updates as $processor => $path) {
    $result = updateImagePath($db, $processor, $path, 'Processor');
    echo "<li>Updated $processor: $result records changed to path: $path</li>";
}

echo "</ul>";

// Fix paths with duplicate category folders
echo "<h2>2. Fixing Duplicate Category Paths</h2>";
echo "<ul>";

$problematicCategories = ['Monitor', 'Hard Drive', 'CPU Cooler', 'Graphics Card'];
foreach ($problematicCategories as $category) {
    $result = fixDuplicateCategoryPaths($db, $category);
    echo "<li>Fixed $category paths: $result records updated</li>";
}

echo "</ul>";

// Check current image paths for problematic categories
echo "<h2>Current Image Paths:</h2>";
try {
    $categories = ['Processor', 'Monitor', 'Hard Drive', 'CPU Cooler', 'Graphics Card'];
    
    foreach ($categories as $category) {
        echo "<h3>$category Images</h3>";
        
        $stmt = $db->prepare("SELECT ID, Name, Image FROM Inventory WHERE Category = :category");
        $stmt->bindParam(':category', $category);
        $stmt->execute();
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table border='1' style='border-collapse: collapse; padding: 8px;'>";
        echo "<tr><th>ID</th><th>Name</th><th>Image Path</th><th>File Exists</th><th>Path Issues</th></tr>";
        
        foreach ($items as $item) {
            $imagePath = isset($item['Image']) ? $item['Image'] : '';
            $fullPath = __DIR__ . '/' . str_replace('../', '', $imagePath);
            $exists = file_exists($fullPath) ? 'Yes' : 'No';
            
            // Check for common path issues
            $issues = [];
            if (strpos($imagePath, '\\') !== false) {
                $issues[] = 'Contains backslashes';
            }
            
            $categoryLower = strtolower($category);
            $doublePathPattern = "components/{$categoryLower}/{$category}/";
            if (strpos($imagePath, $doublePathPattern) !== false) {
                $issues[] = 'Contains duplicate category folder';
            }
            
            if (empty($imagePath)) {
                $issues[] = 'Empty path';
            }
            
            echo "<tr>";
            echo "<td>" . htmlspecialchars($item['ID']) . "</td>";
            echo "<td>" . htmlspecialchars($item['Name']) . "</td>";
            echo "<td>" . htmlspecialchars($imagePath) . "</td>";
            echo "<td>" . $exists . "</td>";
            echo "<td>" . (empty($issues) ? 'None' : implode(', ', $issues)) . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    }
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Create a JavaScript fix for image paths
echo "<h2>JavaScript Fix for Image Paths</h2>";
echo "<p>Copy this code to your browser console to fix image paths at runtime:</p>";
echo "<pre style='background-color: #f5f5f5; padding: 10px; border-radius: 5px; overflow-x: auto;'>";
echo htmlspecialchars("// Fix image paths at runtime
function fixImagePaths() {
    // Find all image elements
    const images = document.querySelectorAll('img');
    
    // Process each image
    images.forEach(img => {
        let src = img.getAttribute('src');
        if (src) {
            // Fix double category in path
            const categories = ['monitor', 'hard drive', 'cpu cooler', 'graphics card'];
            categories.forEach(category => {
                const doublePathPattern = new RegExp(`components\\/${category}\\/${category.split(' ').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ')}\\/`, 'i');
                src = src.replace(doublePathPattern, `components/${category}/`);
            });
            
            // Fix backslashes
            src = src.replace(/\\\\/g, '/');
            
            // Update the image source
            img.setAttribute('src', src);
        }
    });
    
    console.log('Image paths fixed');
}

// Run the fix
fixImagePaths();

// Add a listener to fix paths for dynamically loaded images
const observer = new MutationObserver(mutations => {
    mutations.forEach(mutation => {
        if (mutation.addedNodes) {
            mutation.addedNodes.forEach(node => {
                if (node.tagName === 'IMG') {
                    fixImagePaths();
                }
            });
        }
    });
});

// Start observing the document
observer.observe(document.body, { childList: true, subtree: true });
");
echo "</pre>";

echo "<p><a href='../includes/pc-builder1.php' target='_blank'>Go to PC Builder</a></p>";
?>
