<?php
// Set headers for JSON response
header('Content-Type: application/json');

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Get the absolute path to the database
$dbPath = __DIR__ . '/inventory.db';

// Log the database path for debugging
file_put_contents(__DIR__ . '/debug.log', 'Database path: ' . $dbPath . "\n", FILE_APPEND);

// Database connection
try {
    if (!file_exists($dbPath)) {
        echo json_encode(['error' => 'Database file not found at: ' . $dbPath]);
        exit;
    }
    
    $db = new PDO('sqlite:' . $dbPath);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(['error' => 'Database connection error: ' . $e->getMessage()]);
    exit;
}

// Log the request for debugging
file_put_contents(__DIR__ . '/debug.log', 'Request received: ' . json_encode($_POST) . "\n", FILE_APPEND);

// Validate the category parameter
if (!isset($_POST['category']) || empty($_POST['category'])) {
    echo json_encode(['error' => 'Category parameter is required']);
    exit;
}

$category = $_POST['category'];
file_put_contents(__DIR__ . '/debug.log', 'Processing category: ' . $category . "\n", FILE_APPEND);

// Get items from database based on category
try {
    // First check if the table exists
    $tables = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='Inventory'")->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tables)) {
        echo json_encode(['error' => 'Inventory table does not exist in the database']);
        exit;
    }
    
    $stmt = $db->prepare("SELECT * FROM Inventory WHERE Category = :cat AND Stock_QTY > 0");
    $stmt->bindParam(':cat', $category);
    $stmt->execute();
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Log the number of items found
    file_put_contents(__DIR__ . '/debug.log', 'Found ' . count($items) . ' items for category: ' . $category . "\n", FILE_APPEND);
    
    // Format the response
    $response = [];
    foreach ($items as $item) {
        // Get image path or use default placeholder
        $imagePath = isset($item['Image']) && !empty($item['Image']) 
            ? htmlspecialchars($item['Image']) 
            : "../assets/images/components/" . strtolower($category) . "/default.png";
            
        // If the path starts with "assets/" without "../", add "../" prefix
        if (strpos($imagePath, 'assets/') === 0) {
            $imagePath = '../' . $imagePath;
        }
            
        // Special case for Ryzen 5 5600G
        if (stripos($item['Name'], '5600G') !== false) {
            $imagePath = "../assets/images/components/processor/R5 5600g.jpg";
        }
        // Special case for Ryzen 5 4650G
        else if (stripos($item['Name'], '4650G') !== false) {
            $imagePath = "../assets/images/components/processor/Ryzen 5 4650g.jpg";
        }
        
        $response[] = [
            'id' => $item['ID'],
            'name' => $item['Name'],
            'cash_price' => $item['Cash_Price'],
            'regular_price' => $item['Regular_Price'],
            'specs' => isset($item['Specs']) ? $item['Specs'] : '',
            'image' => $imagePath,
            'stock' => $item['Stock_QTY']
        ];
    }
    
    echo json_encode($response);
} catch(PDOException $e) {
    echo json_encode(['error' => 'Database query error: ' . $e->getMessage()]);
    exit;
}
?>
