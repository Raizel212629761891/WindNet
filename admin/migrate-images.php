<?php
// Include database connection file
require_once 'includes/db_connect.php';

// Get database connection
$db = getDbConnection();

// Define paths
$currentImagesPath = __DIR__ . '/assets/images';
$newImagesPath = __DIR__ . '/Images';

// Create the new images directory if it doesn't exist
if (!file_exists($newImagesPath)) {
    mkdir($newImagesPath, 0755, true);
}

// Create components directory in new images folder
if (!file_exists($newImagesPath . '/components')) {
    mkdir($newImagesPath . '/components', 0755, true);
}

// Handle form submissions
$message = '';
$error = '';
$log = [];

// Function to copy a file from one location to another
function copyImageFile($source, $destination) {
    // Create destination directory if it doesn't exist
    $destDir = dirname($destination);
    if (!file_exists($destDir)) {
        mkdir($destDir, 0755, true);
    }
    
    return copy($source, $destination);
}

// Function to check if file exists
function checkFileExists($path, $basePath = '') {
    if (empty($basePath)) {
        $basePath = __DIR__;
    }
    
    // Remove '../' from the beginning if present
    $localPath = str_replace('../', '', $path);
    // Get the full server path
    $fullPath = $basePath . '/' . $localPath;
    return file_exists($fullPath);
}

// Handle migration process
if (isset($_POST['migrate'])) {
    try {
        // Get all items with image paths
        $stmt = $db->query("SELECT ID, Category, Name, Image FROM Inventory WHERE Image IS NOT NULL AND Image != ''");
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $migrated = 0;
        $errors = 0;
        
        foreach ($items as $item) {
            $currentPath = $item['Image'];
            $category = $item['Category'];
            $categoryDir = strtolower($category);
            
            // Skip if image path is empty
            if (empty($currentPath)) {
                $log[] = "Skipped item #{$item['ID']} ({$item['Name']}): Empty image path";
                continue;
            }
            
            // Create category directory in new images folder if it doesn't exist
            $newCategoryPath = $newImagesPath . '/components/' . $categoryDir;
            if (!file_exists($newCategoryPath)) {
                mkdir($newCategoryPath, 0755, true);
            }
            
            // Get filename from current path
            $filename = basename($currentPath);
            
            // Construct full source path
            $sourcePath = __DIR__ . '/' . str_replace('../', '', $currentPath);
            
            // Construct new path
            $newRelativePath = "Images/components/{$categoryDir}/{$filename}";
            $newFullPath = __DIR__ . '/' . $newRelativePath;
            
            // Check if source file exists
            if (checkFileExists($currentPath)) {
                // Copy file to new location
                if (copyImageFile($sourcePath, $newFullPath)) {
                    // Update database with new path
                    $updateStmt = $db->prepare("UPDATE Inventory SET Image = :newPath WHERE ID = :id");
                    $updateStmt->bindParam(':newPath', $newRelativePath);
                    $updateStmt->bindParam(':id', $item['ID']);
                    $updateStmt->execute();
                    
                    $log[] = "Migrated item #{$item['ID']} ({$item['Name']}): {$currentPath} → {$newRelativePath}";
                    $migrated++;
                } else {
                    $log[] = "Error copying file for item #{$item['ID']} ({$item['Name']}): {$sourcePath} → {$newFullPath}";
                    $errors++;
                }
            } else {
                $log[] = "Source file not found for item #{$item['ID']} ({$item['Name']}): {$sourcePath}";
                $errors++;
            }
        }
        
        $message = "Migration completed: {$migrated} files migrated, {$errors} errors.";
    } catch(PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }
}

// Get migration statistics
$stats = [];
try {
    // Count total items with images
    $stmt = $db->query("SELECT COUNT(*) as total FROM Inventory WHERE Image IS NOT NULL AND Image != ''");
    $stats['total'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Count items with images starting with 'Images/'
    $stmt = $db->query("SELECT COUNT(*) as migrated FROM Inventory WHERE Image LIKE 'Images/%'");
    $stats['migrated'] = $stmt->fetch(PDO::FETCH_ASSOC)['migrated'];
    
    // Count items with images not starting with 'Images/'
    $stmt = $db->query("SELECT COUNT(*) as not_migrated FROM Inventory WHERE Image IS NOT NULL AND Image != '' AND Image NOT LIKE 'Images/%'");
    $stats['not_migrated'] = $stmt->fetch(PDO::FETCH_ASSOC)['not_migrated'];
} catch(PDOException $e) {
    $error = "Error getting statistics: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wind Net Image Migration</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-4">
        <h1 class="text-3xl font-bold mb-6 text-center text-blue-600">Wind Net Image Migration Tool</h1>
        
        <?php if (!empty($message)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Migration Stats -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-xl font-semibold mb-4">Migration Statistics</h2>
                <div class="mb-4">
                    <div class="flex justify-between mb-2">
                        <span>Total items with images:</span>
                        <span class="font-semibold"><?php echo isset($stats['total']) ? $stats['total'] : 'N/A'; ?></span>
                    </div>
                    <div class="flex justify-between mb-2">
                        <span>Migrated to new format:</span>
                        <span class="font-semibold text-green-600"><?php echo isset($stats['migrated']) ? $stats['migrated'] : 'N/A'; ?></span>
                    </div>
                    <div class="flex justify-between mb-2">
                        <span>Not yet migrated:</span>
                        <span class="font-semibold text-red-600"><?php echo isset($stats['not_migrated']) ? $stats['not_migrated'] : 'N/A'; ?></span>
                    </div>
                    
                    <?php if (isset($stats['total']) && $stats['total'] > 0): ?>
                        <div class="w-full bg-gray-200 rounded-full h-2.5 mt-4">
                            <div class="bg-blue-600 h-2.5 rounded-full" style="width: <?php echo ($stats['migrated'] / $stats['total']) * 100; ?>%"></div>
                        </div>
                        <p class="text-sm text-gray-600 mt-1 text-center">
                            <?php echo round(($stats['migrated'] / $stats['total']) * 100); ?>% Complete
                        </p>
                    <?php endif; ?>
                </div>
                
                <form action="" method="post" class="mt-6">
                    <button type="submit" name="migrate" class="w-full bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600" <?php echo (isset($stats['not_migrated']) && $stats['not_migrated'] == 0) ? 'disabled' : ''; ?>>
                        <?php echo (isset($stats['not_migrated']) && $stats['not_migrated'] == 0) ? 'All Images Migrated' : 'Start Migration'; ?>
                    </button>
                </form>
            </div>
            
            <!-- Migration Log -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-xl font-semibold mb-4">Migration Log</h2>
                <?php if (!empty($log)): ?>
                    <div class="bg-gray-100 p-4 rounded-lg max-h-96 overflow-y-auto">
                        <?php foreach ($log as $entry): ?>
                            <div class="mb-2 text-sm">
                                <?php if (strpos($entry, 'Error') !== false || strpos($entry, 'not found') !== false): ?>
                                    <span class="text-red-600"><?php echo htmlspecialchars($entry); ?></span>
                                <?php elseif (strpos($entry, 'Skipped') !== false): ?>
                                    <span class="text-yellow-600"><?php echo htmlspecialchars($entry); ?></span>
                                <?php else: ?>
                                    <span class="text-green-600"><?php echo htmlspecialchars($entry); ?></span>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-gray-600">No migration has been performed yet.</p>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="mt-6 text-center">
            <a href="image-manager.php" class="inline-block bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 mr-4">Go to Image Manager</a>
            <a href="includes/pc-builder1.php" class="inline-block bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600">Go to PC Builder</a>
        </div>
    </div>
</body>
</html>
