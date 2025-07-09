<?php
require_once 'Builder/db_connect.php';

// Get database connection
$db = getDbConnection();

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    $componentId = $_POST['component_id'];
    
    // Get component details to get the category
    $stmt = $db->prepare("SELECT Category FROM Inventory WHERE ID = ?");
    $stmt->execute([$componentId]);
    $component = $stmt->fetch();
    $category = $component['Category'];
    
    $uploadDir = 'Images/components/' . strtolower($category) . '/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $fileName = basename($_FILES['image']['name']);
    $targetPath = $uploadDir . $fileName;
    
    if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
        // Update database with relative path
        $imagePath = 'Images/components/' . strtolower($category) . '/' . $fileName;
        $stmt = $db->prepare("UPDATE Inventory SET Image = ? WHERE ID = ?");
        $stmt->execute([$imagePath, $componentId]);
        $success = "Image uploaded successfully!";
        $uploadedComponentId = $componentId;
        $uploadedImagePath = $imagePath;
    } else {
        $error = "Failed to upload image.";
    }
}

// Get all components with their current images
$stmt = $db->query("SELECT ID, Category, Name, Image FROM Inventory ORDER BY Category, Name");
$components = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group components by category
$componentsByCategory = [];
foreach ($components as $component) {
    $componentsByCategory[$component['Category']][] = $component;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Component Image Manager</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-8">Component Image Manager</h1>
        
        <?php if (isset($success)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <!-- Image Upload Form -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-xl font-semibold mb-4">Upload New Image</h2>
            <form id="uploadForm" method="POST" enctype="multipart/form-data" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Component</label>
                    <select name="component_id" id="componentSelect" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Select a component</option>
                        <?php foreach ($components as $component): ?>
                            <option value="<?php echo $component['ID']; ?>">
                                <?php echo htmlspecialchars($component['Category'] . ' - ' . $component['Name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Image File</label>
                    <input type="file" name="image" required accept="image/*" class="mt-1 block w-full">
                </div>
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                    Upload Image
                </button>
            </form>
        </div>

        <!-- Component Images List -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Current Component Images</h2>
            <?php foreach ($componentsByCategory as $category => $items): ?>
                <div class="mb-8">
                    <h3 class="text-lg font-semibold mb-4"><?php echo htmlspecialchars($category); ?></h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <?php foreach ($items as $component): ?>
                            <div class="border rounded-lg p-4">
                                <div class="mb-2">
                                    <strong><?php echo htmlspecialchars($component['Name']); ?></strong>
                                </div>
                                <div class="mb-2 image-preview" data-component-id="<?php echo $component['ID']; ?>">
                                    <?php if ($component['Image']): ?>
                                        <img src="<?php echo htmlspecialchars($component['Image']); ?>" 
                                             alt="<?php echo htmlspecialchars($component['Name']); ?>"
                                             class="w-full h-32 object-contain bg-gray-100 rounded">
                                    <?php else: ?>
                                        <div class="text-sm text-red-500">
                                            No image set
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="text-sm text-gray-600">
                                    Path: <?php echo htmlspecialchars($component['Image']); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <script>
    // After upload, update the preview for the uploaded component
    <?php if (isset($success) && isset($uploadedComponentId) && isset($uploadedImagePath)): ?>
        document.addEventListener('DOMContentLoaded', function() {
            var previewDiv = document.querySelector('.image-preview[data-component-id="<?php echo $uploadedComponentId; ?>"]');
            if (previewDiv) {
                previewDiv.innerHTML = `<img src="<?php echo htmlspecialchars($uploadedImagePath); ?>?t=<?php echo time(); ?>" class="w-full h-32 object-contain bg-gray-100 rounded">`;
            }
        });
    <?php endif; ?>
    </script>
</body>
</html> 