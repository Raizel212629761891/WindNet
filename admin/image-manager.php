<?php
// Include database connection file
require_once 'includes/db_connect.php';

// Get database connection
$db = getDbConnection();

// Define paths
$currentImagesPath = __DIR__ . '/assets/images';
$newImagesPath = __DIR__ . '/Images'; // Using 'Images' instead of 'Images 1'

// Create the new images directory if it doesn't exist
if (!file_exists($newImagesPath)) {
    mkdir($newImagesPath, 0755, true);
}

// Handle form submissions
$message = '';
$error = '';

// Handle image upload
if (isset($_POST['upload'])) {
    $category = $_POST['category'];
    $targetDir = $newImagesPath . '/components/' . strtolower($category) . '/';
    
    // Create directory if it doesn't exist
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0755, true);
    }
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $filename = basename($_FILES['image']['name']);
        $targetFile = $targetDir . $filename;
        
        // Check if file already exists
        if (file_exists($targetFile)) {
            $error = "File already exists.";
        } else {
            // Try to upload file
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                $message = "File uploaded successfully.";
            } else {
                $error = "Error uploading file.";
            }
        }
    } else {
        $error = "Error: " . $_FILES['image']['error'];
    }
}

// Handle database update
if (isset($_POST['update_db'])) {
    $itemId = $_POST['item_id'];
    $newPath = $_POST['new_path'];
    
    try {
        $stmt = $db->prepare("UPDATE Inventory SET Image = :image WHERE ID = :id");
        $stmt->bindParam(':image', $newPath);
        $stmt->bindParam(':id', $itemId);
        $stmt->execute();
        $message = "Database updated successfully.";
    } catch(PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }
}

// Get categories
$categories = [];
try {
    $stmt = $db->query("SELECT DISTINCT Category FROM Inventory ORDER BY Category");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $categories[] = $row['Category'];
    }
} catch(PDOException $e) {
    $error = "Error fetching categories: " . $e->getMessage();
}

// Get items for a specific category
$selectedCategory = isset($_GET['category']) ? $_GET['category'] : '';
$items = [];

if (!empty($selectedCategory)) {
    try {
        $stmt = $db->prepare("SELECT ID, Name, Image FROM Inventory WHERE Category = :category ORDER BY Name");
        $stmt->bindParam(':category', $selectedCategory);
        $stmt->execute();
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        $error = "Error fetching items: " . $e->getMessage();
    }
}

// Function to check if file exists
function checkFileExists($path, $basePath) {
    // Remove '../' from the beginning if present
    $localPath = str_replace('../', '', $path);
    // Get the full server path
    $fullPath = $basePath . '/' . $localPath;
    return file_exists($fullPath) ? 'Yes' : 'No';
}

// Function to get a list of image files in a directory
function getImagesInDirectory($dir) {
    if (!file_exists($dir)) {
        return "Directory does not exist: $dir";
    }
    
    $images = [];
    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file != '.' && $file != '..' && !is_dir($dir . '/' . $file)) {
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                $images[] = $file;
            }
        }
    }
    
    return $images;
}

// Function to copy a file from one location to another
function copyImageFile($source, $destination) {
    // Create destination directory if it doesn't exist
    $destDir = dirname($destination);
    if (!file_exists($destDir)) {
        mkdir($destDir, 0755, true);
    }
    
    return copy($source, $destination);
}

// Handle copy/move operations
if (isset($_POST['copy_image'])) {
    $sourceFile = $_POST['source_file'];
    $category = $_POST['category'];
    $filename = basename($sourceFile);
    
    // Determine destination directory
    $destDir = $newImagesPath . '/components/' . strtolower($category) . '/';
    
    // Create directory if it doesn't exist
    if (!file_exists($destDir)) {
        mkdir($destDir, 0755, true);
    }
    
    $destFile = $destDir . $filename;
    
    if (copyImageFile($sourceFile, $destFile)) {
        $message = "File copied successfully.";
    } else {
        $error = "Error copying file.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wind Net Image Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-4">
        <h1 class="text-3xl font-bold mb-6 text-center text-blue-600">Wind Net Image Manager</h1>
        
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
            <!-- Category Selection -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-xl font-semibold mb-4">Select Category</h2>
                <form action="" method="get" class="mb-4">
                    <div class="mb-4">
                        <label for="category" class="block text-gray-700 mb-2">Category:</label>
                        <select name="category" id="category" class="w-full px-3 py-2 border rounded-lg" onchange="this.form.submit()">
                            <option value="">-- Select Category --</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo htmlspecialchars($category); ?>" <?php echo ($selectedCategory === $category) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>
                
                <?php if (!empty($selectedCategory)): ?>
                    <!-- Upload New Image -->
                    <h3 class="text-lg font-semibold mb-3">Upload New Image</h3>
                    <form action="" method="post" enctype="multipart/form-data" class="mb-6">
                        <input type="hidden" name="category" value="<?php echo htmlspecialchars($selectedCategory); ?>">
                        <div class="mb-4">
                            <label for="image" class="block text-gray-700 mb-2">Select Image:</label>
                            <input type="file" name="image" id="image" class="w-full px-3 py-2 border rounded-lg" accept="image/*" required>
                        </div>
                        <button type="submit" name="upload" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">Upload Image</button>
                    </form>
                <?php endif; ?>
            </div>
            
            <!-- Database Items -->
            <?php if (!empty($selectedCategory) && !empty($items)): ?>
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h2 class="text-xl font-semibold mb-4">Database Items</h2>
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white">
                            <thead>
                                <tr>
                                    <th class="py-2 px-4 border-b">ID</th>
                                    <th class="py-2 px-4 border-b">Name</th>
                                    <th class="py-2 px-4 border-b">Current Image</th>
                                    <th class="py-2 px-4 border-b">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $item): ?>
                                    <tr>
                                        <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($item['ID']); ?></td>
                                        <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($item['Name']); ?></td>
                                        <td class="py-2 px-4 border-b">
                                            <?php 
                                            $imagePath = isset($item['Image']) ? $item['Image'] : '';
                                            $exists = !empty($imagePath) ? checkFileExists($imagePath, __DIR__) : 'N/A';
                                            echo htmlspecialchars($imagePath) . ' (' . $exists . ')';
                                            ?>
                                        </td>
                                        <td class="py-2 px-4 border-b">
                                            <button type="button" onclick="showUpdateForm(<?php echo $item['ID']; ?>, '<?php echo addslashes($item['Image']); ?>')" class="bg-yellow-500 text-white px-2 py-1 rounded hover:bg-yellow-600 text-sm">Update Path</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if (!empty($selectedCategory)): ?>
            <!-- Image Directories -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                <!-- Current Images Directory -->
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h2 class="text-xl font-semibold mb-4">Current Images</h2>
                    <?php
                    $currentDir = $currentImagesPath . '/components/' . strtolower($selectedCategory);
                    $currentImages = getImagesInDirectory($currentDir);
                    
                    if (is_array($currentImages) && count($currentImages) > 0): ?>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                            <?php foreach ($currentImages as $image): ?>
                                <div class="border rounded-lg p-2">
                                    <img src="assets/images/components/<?php echo strtolower($selectedCategory); ?>/<?php echo $image; ?>" alt="<?php echo $image; ?>" class="w-full h-32 object-contain mb-2">
                                    <p class="text-sm truncate"><?php echo $image; ?></p>
                                    <form action="" method="post" class="mt-2">
                                        <input type="hidden" name="source_file" value="<?php echo $currentDir . '/' . $image; ?>">
                                        <input type="hidden" name="category" value="<?php echo htmlspecialchars($selectedCategory); ?>">
                                        <button type="submit" name="copy_image" class="bg-blue-500 text-white px-2 py-1 rounded hover:bg-blue-600 text-sm w-full">Copy to New</button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p>No images found in this directory.</p>
                    <?php endif; ?>
                </div>
                
                <!-- New Images Directory -->
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h2 class="text-xl font-semibold mb-4">New Images</h2>
                    <?php
                    $newDir = $newImagesPath . '/components/' . strtolower($selectedCategory);
                    $newImages = getImagesInDirectory($newDir);
                    
                    if (is_array($newImages) && count($newImages) > 0): ?>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                            <?php foreach ($newImages as $image): ?>
                                <div class="border rounded-lg p-2">
                                    <img src="Images/components/<?php echo strtolower($selectedCategory); ?>/<?php echo $image; ?>" alt="<?php echo $image; ?>" class="w-full h-32 object-contain mb-2">
                                    <p class="text-sm truncate"><?php echo $image; ?></p>
                                    <button type="button" onclick="useThisImage('Images/components/<?php echo strtolower($selectedCategory); ?>/<?php echo $image; ?>')" class="bg-green-500 text-white px-2 py-1 rounded hover:bg-green-600 text-sm w-full mt-2">Use This Image</button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p>No images found in this directory.</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Update Form Modal -->
        <div id="updateModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
            <div class="bg-white p-6 rounded-lg shadow-md w-full max-w-md">
                <h2 class="text-xl font-semibold mb-4">Update Image Path</h2>
                <form action="" method="post">
                    <input type="hidden" name="item_id" id="update_item_id">
                    <div class="mb-4">
                        <label for="current_path" class="block text-gray-700 mb-2">Current Path:</label>
                        <input type="text" id="current_path" class="w-full px-3 py-2 border rounded-lg bg-gray-100" readonly>
                    </div>
                    <div class="mb-4">
                        <label for="new_path" class="block text-gray-700 mb-2">New Path:</label>
                        <input type="text" name="new_path" id="new_path" class="w-full px-3 py-2 border rounded-lg">
                    </div>
                    <div class="flex justify-end">
                        <button type="button" onclick="closeUpdateForm()" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 mr-2">Cancel</button>
                        <button type="submit" name="update_db" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">Update</button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="mt-6 text-center">
            <a href="includes/pc-builder1.php" class="inline-block bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">Go to PC Builder</a>
        </div>
    </div>
    
    <script>
        function showUpdateForm(itemId, currentPath) {
            document.getElementById('update_item_id').value = itemId;
            document.getElementById('current_path').value = currentPath;
            document.getElementById('new_path').value = currentPath;
            document.getElementById('updateModal').classList.remove('hidden');
        }
        
        function closeUpdateForm() {
            document.getElementById('updateModal').classList.add('hidden');
        }
        
        function useThisImage(path) {
            document.getElementById('new_path').value = path;
        }
    </script>
</body>
</html>
