<?php
// Include database connection file
require_once 'includes/db_connect.php';

// Get database connection
$db = getDbConnection();

// Define paths
$newImagesBasePath = 'Images';

// Handle form submissions
$message = '';
$error = '';
$log = [];

// Function to check if file exists
function checkFileExists($path, $basePath = '') {
    if (empty($basePath)) {
        $basePath = __DIR__;
    }
    
    // Get the full server path
    $fullPath = $basePath . '/' . $path;
    return file_exists($fullPath);
}

// Handle update process
if (isset($_POST['update_paths'])) {
    try {
        // Get all items with image paths
        $stmt = $db->query("SELECT ID, Category, Name, Image FROM Inventory WHERE Image IS NOT NULL AND Image != ''");
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $updated = 0;
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
            
            // Get filename from current path
            $filename = basename($currentPath);
            
            // Construct new path
            $newRelativePath = "{$newImagesBasePath}/components/{$categoryDir}/{$filename}";
            
            // Check if new file exists
            if (checkFileExists($newRelativePath)) {
                // Update database with new path
                $updateStmt = $db->prepare("UPDATE Inventory SET Image = :newPath WHERE ID = :id");
                $updateStmt->bindParam(':newPath', $newRelativePath);
                $updateStmt->bindParam(':id', $item['ID']);
                $updateStmt->execute();
                
                $log[] = "Updated item #{$item['ID']} ({$item['Name']}): {$currentPath} → {$newRelativePath}";
                $updated++;
            } else {
                // Try with different image formats
                $found = false;
                $extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                $filenameWithoutExt = pathinfo($filename, PATHINFO_FILENAME);
                
                foreach ($extensions as $ext) {
                    $altFilename = "{$filenameWithoutExt}.{$ext}";
                    $altPath = "{$newImagesBasePath}/components/{$categoryDir}/{$altFilename}";
                    
                    if (checkFileExists($altPath)) {
                        // Update database with alternative path
                        $updateStmt = $db->prepare("UPDATE Inventory SET Image = :newPath WHERE ID = :id");
                        $updateStmt->bindParam(':newPath', $altPath);
                        $updateStmt->bindParam(':id', $item['ID']);
                        $updateStmt->execute();
                        
                        $log[] = "Updated item #{$item['ID']} ({$item['Name']}) with alternative format: {$currentPath} → {$altPath}";
                        $updated++;
                        $found = true;
                        break;
                    }
                }
                
                if (!$found) {
                    $log[] = "Image file not found for item #{$item['ID']} ({$item['Name']}): {$newRelativePath}";
                    $errors++;
                }
            }
        }
        
        $message = "Update completed: {$updated} paths updated, {$errors} errors.";
    } catch(PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }
}

// Get statistics
$stats = [];
try {
    // Count total items with images
    $stmt = $db->query("SELECT COUNT(*) as total FROM Inventory WHERE Image IS NOT NULL AND Image != ''");
    $stats['total'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Count items with images starting with 'Images/'
    $stmt = $db->query("SELECT COUNT(*) as updated FROM Inventory WHERE Image LIKE 'Images/%'");
    $stats['updated'] = $stmt->fetch(PDO::FETCH_ASSOC)['updated'];
    
    // Count items with images not starting with 'Images/'
    $stmt = $db->query("SELECT COUNT(*) as not_updated FROM Inventory WHERE Image IS NOT NULL AND Image != '' AND Image NOT LIKE 'Images/%'");
    $stats['not_updated'] = $stmt->fetch(PDO::FETCH_ASSOC)['not_updated'];
} catch(PDOException $e) {
    $error = "Error getting statistics: " . $e->getMessage();
}

// Get sample items for preview
$sampleItems = [];
try {
    $stmt = $db->query("SELECT ID, Category, Name, Image FROM Inventory WHERE Image IS NOT NULL AND Image != '' LIMIT 10");
    $sampleItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Error fetching sample items: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wind Net Image Path Update</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-4">
        <h1 class="text-3xl font-bold mb-6 text-center text-blue-600">Wind Net Image Path Update Tool</h1>
        
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
            <!-- Update Stats -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-xl font-semibold mb-4">Update Statistics</h2>
                <div class="mb-4">
                    <div class="flex justify-between mb-2">
                        <span>Total items with images:</span>
                        <span class="font-semibold"><?php echo isset($stats['total']) ? $stats['total'] : 'N/A'; ?></span>
                    </div>
                    <div class="flex justify-between mb-2">
                        <span>Updated to new format:</span>
                        <span class="font-semibold text-green-600"><?php echo isset($stats['updated']) ? $stats['updated'] : 'N/A'; ?></span>
                    </div>
                    <div class="flex justify-between mb-2">
                        <span>Not yet updated:</span>
                        <span class="font-semibold text-red-600"><?php echo isset($stats['not_updated']) ? $stats['not_updated'] : 'N/A'; ?></span>
                    </div>
                    
                    <?php if (isset($stats['total']) && $stats['total'] > 0): ?>
                        <div class="w-full bg-gray-200 rounded-full h-2.5 mt-4">
                            <div class="bg-blue-600 h-2.5 rounded-full" style="width: <?php echo ($stats['updated'] / $stats['total']) * 100; ?>%"></div>
                        </div>
                        <p class="text-sm text-gray-600 mt-1 text-center">
                            <?php echo round(($stats['updated'] / $stats['total']) * 100); ?>% Complete
                        </p>
                    <?php endif; ?>
                </div>
                
                <form action="" method="post" class="mt-6">
                    <button type="submit" name="update_paths" class="w-full bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600" <?php echo (isset($stats['not_updated']) && $stats['not_updated'] == 0) ? 'disabled' : ''; ?>>
                        <?php echo (isset($stats['not_updated']) && $stats['not_updated'] == 0) ? 'All Paths Updated' : 'Update Image Paths'; ?>
                    </button>
                </form>
                
                <div class="mt-6">
                    <h3 class="text-lg font-semibold mb-2">How This Works</h3>
                    <p class="text-sm text-gray-600">
                        This tool updates your database to use images from <code class="bg-gray-200 px-1 rounded">Images/components/[category]/</code> instead of the old paths. It will:
                    </p>
                    <ul class="list-disc list-inside text-sm text-gray-600 mt-2">
                        <li>Keep the same filename for each image</li>
                        <li>Try alternative file extensions if the exact file isn't found</li>
                        <li>Update all database records to point to the new location</li>
                    </ul>
                </div>
            </div>
            
            <!-- Update Log -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-xl font-semibold mb-4">Update Log</h2>
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
                    <p class="text-gray-600">No updates have been performed yet.</p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Sample Items -->
        <div class="mt-6 bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-semibold mb-4">Sample Items</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white">
                    <thead>
                        <tr>
                            <th class="py-2 px-4 border-b">ID</th>
                            <th class="py-2 px-4 border-b">Category</th>
                            <th class="py-2 px-4 border-b">Name</th>
                            <th class="py-2 px-4 border-b">Current Image Path</th>
                            <th class="py-2 px-4 border-b">Image Preview</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sampleItems as $item): ?>
                            <tr>
                                <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($item['ID']); ?></td>
                                <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($item['Category']); ?></td>
                                <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($item['Name']); ?></td>
                                <td class="py-2 px-4 border-b">
                                    <code class="bg-gray-100 px-1 py-1 rounded text-xs"><?php echo htmlspecialchars($item['Image']); ?></code>
                                </td>
                                <td class="py-2 px-4 border-b">
                                    <?php if (!empty($item['Image']) && checkFileExists($item['Image'])): ?>
                                        <img src="<?php echo htmlspecialchars($item['Image']); ?>" alt="<?php echo htmlspecialchars($item['Name']); ?>" class="w-16 h-16 object-contain">
                                    <?php else: ?>
                                        <span class="text-red-500 text-xs">Image not found</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="mt-6 text-center">
            <a href="image-manager.php" class="inline-block bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 mr-4">Image Manager</a>
            <a href="includes/pc-builder1.php" class="inline-block bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600">Go to PC Builder</a>
        </div>
    </div>
</body>
</html>
