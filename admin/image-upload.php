<?php
// Include database connection file
require_once 'includes/db_connect.php';

// Get database connection
$db = getDbConnection();

// Define paths
$imagesPath = __DIR__ . '/Images';

// Create the images directory if it doesn't exist
if (!file_exists($imagesPath)) {
    mkdir($imagesPath, 0755, true);
}

// Create components directory in images folder
if (!file_exists($imagesPath . '/components')) {
    mkdir($imagesPath . '/components', 0755, true);
}

// Handle form submissions
$message = '';
$error = '';

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

// Handle image upload
if (isset($_POST['upload'])) {
    $category = $_POST['category'];
    $itemId = $_POST['item_id'];
    $targetDir = $imagesPath . '/components/' . strtolower($category) . '/';
    
    // Create directory if it doesn't exist
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0755, true);
    }
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        // Generate a unique filename based on product name and timestamp
        $itemName = '';
        try {
            $stmt = $db->prepare("SELECT Name FROM Inventory WHERE ID = :id");
            $stmt->bindParam(':id', $itemId);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result) {
                $itemName = $result['Name'];
            }
        } catch(PDOException $e) {
            $error = "Error fetching item name: " . $e->getMessage();
        }
        
        // Clean the filename
        $cleanName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $itemName);
        $cleanName = strtolower($cleanName);
        
        // Get file extension
        $fileExt = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        
        // Create unique filename
        $filename = $cleanName . '_' . time() . '.' . $fileExt;
        $targetFile = $targetDir . $filename;
        
        // Try to upload file
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            // Update database with new image path
            $newPath = "Images/components/" . strtolower($category) . "/" . $filename;
            try {
                $stmt = $db->prepare("UPDATE Inventory SET Image = :image WHERE ID = :id");
                $stmt->bindParam(':image', $newPath);
                $stmt->bindParam(':id', $itemId);
                $stmt->execute();
                $message = "Image uploaded and database updated successfully.";
            } catch(PDOException $e) {
                $error = "Database error: " . $e->getMessage();
            }
        } else {
            $error = "Error uploading file.";
        }
    } else {
        $error = "Error: " . $_FILES['image']['error'];
    }
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
function checkFileExists($path) {
    if (empty($path)) return false;
    
    // Remove '../' from the beginning if present
    $localPath = str_replace('../', '', $path);
    // Get the full server path
    $fullPath = __DIR__ . '/' . $localPath;
    return file_exists($fullPath);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wind Net Image Upload</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-4">
        <h1 class="text-3xl font-bold mb-6 text-center text-blue-600">Wind Net Image Upload</h1>
        
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
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
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
            </div>
            
            <!-- Items List -->
            <?php if (!empty($selectedCategory) && !empty($items)): ?>
                <div class="bg-white p-6 rounded-lg shadow-md md:col-span-2">
                    <h2 class="text-xl font-semibold mb-4">Items in <?php echo htmlspecialchars($selectedCategory); ?></h2>
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
                                            <?php if (!empty($item['Image']) && checkFileExists($item['Image'])): ?>
                                                <div class="flex items-center">
                                                    <img src="<?php echo htmlspecialchars($item['Image']); ?>" alt="<?php echo htmlspecialchars($item['Name']); ?>" class="w-16 h-16 object-contain mr-2">
                                                    <span class="text-xs text-gray-500"><?php echo htmlspecialchars($item['Image']); ?></span>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-red-500">No image or file not found</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-2 px-4 border-b">
                                            <button type="button" onclick="showUploadForm(<?php echo $item['ID']; ?>, '<?php echo addslashes($item['Name']); ?>')" class="bg-blue-500 text-white px-2 py-1 rounded hover:bg-blue-600 text-sm">Upload Image</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Upload Form Modal -->
        <div id="uploadModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
            <div class="bg-white p-6 rounded-lg shadow-md w-full max-w-md">
                <h2 class="text-xl font-semibold mb-4">Upload Image</h2>
                <form action="" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="category" value="<?php echo htmlspecialchars($selectedCategory); ?>">
                    <input type="hidden" name="item_id" id="upload_item_id">
                    
                    <div class="mb-4">
                        <p class="text-gray-700 mb-2">Product: <span id="upload_item_name" class="font-semibold"></span></p>
                    </div>
                    
                    <div class="mb-4">
                        <label for="image" class="block text-gray-700 mb-2">Select Image:</label>
                        <input type="file" name="image" id="image" class="w-full px-3 py-2 border rounded-lg" accept="image/*" required>
                    </div>
                    
                    <div class="flex justify-end">
                        <button type="button" onclick="closeUploadForm()" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 mr-2">Cancel</button>
                        <button type="submit" name="upload" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">Upload</button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="mt-6 text-center">
            <a href="image-manager.php" class="inline-block bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 mr-4">Image Manager</a>
            <a href="migrate-images.php" class="inline-block bg-yellow-500 text-white px-4 py-2 rounded-lg hover:bg-yellow-600 mr-4">Migration Tool</a>
            <a href="includes/pc-builder1.php" class="inline-block bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600">PC Builder</a>
        </div>
    </div>
    
    <script>
        function showUploadForm(itemId, itemName) {
            document.getElementById('upload_item_id').value = itemId;
            document.getElementById('upload_item_name').textContent = itemName;
            document.getElementById('uploadModal').classList.remove('hidden');
        }
        
        function closeUploadForm() {
            document.getElementById('uploadModal').classList.add('hidden');
        }
    </script>
</body>
</html>
