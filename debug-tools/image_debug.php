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
function updateImagePaths($db, $category, $imageName) {
    try {
        $stmt = $db->prepare("UPDATE Inventory SET Image_Path = :path WHERE Category = :category");
        $path = "assets/images/components/" . strtolower($category) . "/" . $imageName;
        $stmt->bindParam(':path', $path);
        $stmt->bindParam(':category', $category);
        $stmt->execute();
        return $stmt->rowCount();
    } catch(PDOException $e) {
        return "Error: " . $e->getMessage();
    }
}

// Function to check if an image exists
function imageExists($path) {
    $fullPath = __DIR__ . '/' . $path;
    $exists = file_exists($fullPath);
    $size = $exists ? filesize($fullPath) : 0;
    return [
        'exists' => $exists,
        'path' => $fullPath,
        'size' => $size,
        'web_path' => $path
    ];
}

// Handle form submission for updating images
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_images'])) {
        $category = $_POST['category'];
        $imageName = $_POST['image_name'];
        $result = updateImagePaths($db, $category, $imageName);
        $message = "Updated $result records for $category with image: $imageName";
    }
    
    // Handle file upload
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] == 0) {
        $category = $_POST['upload_category'];
        $targetDir = "assets/images/components/" . strtolower($category) . "/";
        
        // Create directory if it doesn't exist
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        
        $fileName = basename($_FILES["image_file"]["name"]);
        $targetFile = $targetDir . $fileName;
        
        // Upload the file
        if (move_uploaded_file($_FILES["image_file"]["tmp_name"], $targetFile)) {
            $uploadMessage = "File uploaded successfully: $fileName";
            
            // Update database with new image path
            $result = updateImagePaths($db, $category, $fileName);
            $uploadMessage .= "<br>Updated $result records in the database.";
        } else {
            $uploadMessage = "Error uploading file.";
        }
    }
}

// Get current image paths from database
$categories = ['Processor', 'Motherboard', 'RAM', 'Storage', 'GPU', 'Power Supply', 'Casing'];
$imagePaths = [];

foreach ($categories as $category) {
    try {
        $stmt = $db->prepare("SELECT Name, Image_Path FROM Inventory WHERE Category = :category");
        $stmt->bindParam(':category', $category);
        $stmt->execute();
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $imagePaths[$category] = $items;
    } catch(PDOException $e) {
        echo "Error getting $category: " . $e->getMessage();
    }
}

// Create placeholder image if it doesn't exist
$placeholderPath = "assets/images/components/placeholder.png";
if (!file_exists($placeholderPath)) {
    // Create a simple placeholder image
    $img = imagecreatetruecolor(300, 300);
    $bgColor = imagecolorallocate($img, 40, 40, 40);
    $textColor = imagecolorallocate($img, 200, 200, 200);
    imagefill($img, 0, 0, $bgColor);
    imagestring($img, 5, 75, 140, "No Image Available", $textColor);
    imagepng($img, $placeholderPath);
    imagedestroy($img);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Image Debug Tool - Wind Net</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background-color: #1f2937;
            color: #f3f4f6;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .card {
            background-color: #374151;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #4b5563;
        }
        th {
            background-color: #2d3748;
        }
        .btn {
            background-color: #3b82f6;
            color: white;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            border: none;
        }
        .btn:hover {
            background-color: #2563eb;
        }
        .btn-red {
            background-color: #ef4444;
        }
        .btn-red:hover {
            background-color: #dc2626;
        }
        .btn-green {
            background-color: #10b981;
        }
        .btn-green:hover {
            background-color: #059669;
        }
        input, select {
            background-color: #4b5563;
            border: 1px solid #6b7280;
            color: white;
            padding: 8px;
            border-radius: 4px;
        }
        .image-preview {
            max-width: 100px;
            max-height: 100px;
            object-fit: contain;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-3xl font-bold mb-6">Image Debug Tool</h1>
        
        <!-- Upload Image Form -->
        <div class="card">
            <h2 class="text-xl font-bold mb-4">Upload Component Image</h2>
            <form method="post" enctype="multipart/form-data">
                <div class="flex flex-wrap gap-4 items-end">
                    <div>
                        <label class="block mb-2">Category</label>
                        <select name="upload_category" required>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category ?>"><?= $category ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block mb-2">Image File</label>
                        <input type="file" name="image_file" required accept="image/*">
                    </div>
                    <div>
                        <button type="submit" class="btn btn-green">Upload & Update</button>
                    </div>
                </div>
                <?php if (isset($uploadMessage)): ?>
                    <div class="mt-4 p-3 bg-green-800 rounded">
                        <?= $uploadMessage ?>
                    </div>
                <?php endif; ?>
            </form>
        </div>
        
        <!-- Update Image Paths Form -->
        <div class="card">
            <h2 class="text-xl font-bold mb-4">Update Image Paths</h2>
            <form method="post">
                <div class="flex flex-wrap gap-4 items-end">
                    <div>
                        <label class="block mb-2">Category</label>
                        <select name="category" required>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category ?>"><?= $category ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block mb-2">Image Name (with extension)</label>
                        <input type="text" name="image_name" required placeholder="example.png">
                    </div>
                    <div>
                        <button type="submit" name="update_images" class="btn">Update All Items in Category</button>
                    </div>
                </div>
                <?php if (isset($message)): ?>
                    <div class="mt-4 p-3 bg-blue-800 rounded">
                        <?= $message ?>
                    </div>
                <?php endif; ?>
            </form>
        </div>
        
        <!-- Debug Information -->
        <div class="card">
            <h2 class="text-xl font-bold mb-4">Debug Information</h2>
            
            <h3 class="text-lg font-semibold mt-4 mb-2">Placeholder Image</h3>
            <?php 
            $placeholderInfo = imageExists("assets/images/components/placeholder.png");
            ?>
            <div class="flex items-center gap-4 mb-4">
                <img src="<?= $placeholderInfo['web_path'] ?>" alt="Placeholder" class="image-preview">
                <div>
                    <p>Path: <?= $placeholderInfo['path'] ?></p>
                    <p>Exists: <?= $placeholderInfo['exists'] ? 'Yes' : 'No' ?></p>
                    <p>Size: <?= $placeholderInfo['size'] ?> bytes</p>
                </div>
            </div>
            
            <h3 class="text-lg font-semibold mt-4 mb-2">Component Directories</h3>
            <table>
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Directory</th>
                        <th>Exists</th>
                        <th>Files</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $category): 
                        $dirPath = "assets/images/components/" . strtolower(str_replace(' ', ' ', $category));
                        $dirExists = is_dir($dirPath);
                        $files = $dirExists ? scandir($dirPath) : [];
                        $files = array_filter($files, function($file) {
                            return $file != '.' && $file != '..';
                        });
                    ?>
                        <tr>
                            <td><?= $category ?></td>
                            <td><?= $dirPath ?></td>
                            <td><?= $dirExists ? 'Yes' : 'No' ?></td>
                            <td><?= count($files) ?> files</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <h3 class="text-lg font-semibold mt-6 mb-2">Current Image Paths in Database</h3>
            <?php foreach ($categories as $category): ?>
                <h4 class="font-medium mt-4 mb-2"><?= $category ?></h4>
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Image Path</th>
                            <th>Image Exists</th>
                            <th>Preview</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($imagePaths[$category] as $item): 
                            $imageInfo = imageExists($item['Image_Path']);
                        ?>
                            <tr>
                                <td><?= $item['Name'] ?></td>
                                <td><?= $item['Image_Path'] ?></td>
                                <td><?= $imageInfo['exists'] ? 'Yes' : 'No' ?></td>
                                <td>
                                    <?php if ($imageInfo['exists']): ?>
                                        <img src="<?= $item['Image_Path'] ?>" alt="<?= $item['Name'] ?>" class="image-preview">
                                    <?php else: ?>
                                        <span class="text-red-500">Not found</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endforeach; ?>
        </div>
        
        <!-- JavaScript Debug -->
        <div class="card">
            <h2 class="text-xl font-bold mb-4">JavaScript Image Loading Test</h2>
            <div class="flex gap-4">
                <div class="w-1/2">
                    <div class="component-image-container h-40 bg-gray-900 rounded-lg flex items-center justify-center overflow-hidden mb-4">
                        <img 
                            id="test-image" 
                            src="assets/images/components/placeholder.png" 
                            alt="Test Image" 
                            class="component-image max-h-full max-w-full object-contain p-2 transition-all duration-300"
                        >
                    </div>
                    <select id="test-select" class="w-full p-2 rounded-lg bg-gray-900 text-white border border-gray-700">
                        <option value="assets/images/components/placeholder.png">Placeholder</option>
                        <?php foreach ($categories as $category): 
                            $dirPath = "assets/images/components/" . strtolower(str_replace(' ', ' ', $category));
                            if (is_dir($dirPath)) {
                                $files = scandir($dirPath);
                                foreach ($files as $file) {
                                    if ($file != '.' && $file != '..') {
                                        $path = $dirPath . '/' . $file;
                                        echo "<option value=\"$path\">$category - $file</option>";
                                    }
                                }
                            }
                        ?>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="w-1/2">
                    <div id="debug-output" class="bg-gray-900 p-4 rounded-lg h-60 overflow-auto">
                        <p>Debug output will appear here...</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mt-6">
            <a href="index.php" class="btn">Back to PC Builder</a>
        </div>
    </div>
    
    <script>
        // Test image loading
        document.addEventListener('DOMContentLoaded', function() {
            const testImage = document.getElementById('test-image');
            const testSelect = document.getElementById('test-select');
            const debugOutput = document.getElementById('debug-output');
            
            function log(message) {
                const time = new Date().toLocaleTimeString();
                debugOutput.innerHTML += `<p>[${time}] ${message}</p>`;
                debugOutput.scrollTop = debugOutput.scrollHeight;
            }
            
            log('JavaScript initialized');
            
            testSelect.addEventListener('change', function() {
                const imagePath = this.value;
                log(`Selected image: ${imagePath}`);
                
                // Add fade-out effect
                testImage.classList.add('opacity-0');
                log('Added opacity-0 class');
                
                // Change image source after a short delay
                setTimeout(() => {
                    log(`Setting src to: ${imagePath}`);
                    testImage.src = imagePath;
                    
                    // Handle image loading errors
                    testImage.onerror = function() {
                        log('ERROR: Image failed to load');
                        this.src = 'assets/images/components/placeholder.png';
                        this.classList.remove('opacity-0');
                    };
                    
                    // When image loads successfully, fade it in
                    testImage.onload = function() {
                        log('SUCCESS: Image loaded successfully');
                        this.classList.remove('opacity-0');
                    };
                }, 200);
            });
            
            // Add CSS for transitions
            const style = document.createElement('style');
            style.textContent = `
                .component-image {
                    opacity: 1;
                    transition: opacity 0.3s ease-in-out;
                }
                .component-image.opacity-0 {
                    opacity: 0;
                }
            `;
            document.head.appendChild(style);
            log('Added transition styles');
        });
    </script>
</body>
</html>
