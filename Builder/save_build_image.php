<?php
// Include database connection
require_once 'db_connect.php';

// Set response header to JSON
header('Content-Type: application/json');

// Get the posted JSON data
$jsonData = file_get_contents('php://input');
$data = json_decode($jsonData, true);

// Validate required parameters
if (!isset($data['image_data']) || !isset($data['quotation_id']) || !is_numeric($data['quotation_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing or invalid parameters']);
    exit;
}

// Extract base64 image data
$imageData = $data['image_data'];
$quotationId = (int) $data['quotation_id'];

// Check if the image data is valid
if (strpos($imageData, 'data:image/png;base64,') !== 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid image format']);
    exit;
}

// Extract the actual base64 data
$imageData = str_replace('data:image/png;base64,', '', $imageData);
$imageData = str_replace(' ', '+', $imageData);
$decodedImage = base64_decode($imageData);

if ($decodedImage === false) {
    echo json_encode(['success' => false, 'message' => 'Failed to decode image']);
    exit;
}

// Create a new image from the decoded data
$image = imagecreatefromstring($decodedImage);
if ($image === false) {
    echo json_encode(['success' => false, 'message' => 'Failed to create image']);
    exit;
}

// Get image dimensions
$width = imagesx($image);
$height = imagesy($image);

// Create a new image with additional space for header
$newHeight = $height + 200; // Add space for header
$newImage = imagecreatetruecolor($width, $newHeight);

// Set background color (dark gray)
$bgColor = imagecolorallocate($newImage, 40, 40, 40);
imagefill($newImage, 0, 0, $bgColor);

// Copy the original image to the new image
imagecopy($newImage, $image, 0, 200, 0, 0, $width, $height);

// Set text color (white)
$textColor = imagecolorallocate($newImage, 255, 255, 255);

// Add company information
$companyInfo = [
    "Wind Net PC - Quotation #" . $quotationId,
    "Granja Corner Barcelona St, Brgy 1, Lucena City",
    "Facebook.com/windnetpc | windnetpc.com",
    "0912-039-0713(smart/viber) | (042) 719-2279 (PLDT)",
    "For faster response send us a message/call via messenger"
];

// Get payment method from database
try {
    $db = getDbConnection();
    $stmt = $db->prepare("SELECT payment_method FROM quotation WHERE id = :id");
    $stmt->bindParam(':id', $quotationId, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        $paymentMethod = $result['payment_method'];
        $paymentText = "This customer selected " . ucfirst($paymentMethod) . " Payment";
        $companyInfo[] = $paymentText;
    }
} catch (PDOException $e) {
    error_log("Error getting payment method: " . $e->getMessage());
}

// Add text to image
$font = 5; // Built-in font
$lineHeight = 20;
$y = 20;

foreach ($companyInfo as $line) {
    // Center the text
    $textWidth = strlen($line) * 8; // Approximate width
    $x = ($width - $textWidth) / 2;
    
    imagestring($newImage, $font, $x, $y, $line, $textColor);
    $y += $lineHeight;
}

// Create directory if it doesn't exist
$directory = '../quotation_images';
if (!file_exists($directory)) {
    mkdir($directory, 0777, true);
}

// Generate a unique filename
$filename = $directory . '/quotation_' . $quotationId . '_' . time() . '.png';

// Save the new image
if (imagepng($newImage, $filename)) {
    // Update database with image path if needed
    try {
        $db = getDbConnection();
        $stmt = $db->prepare("UPDATE quotation SET image_path = :path WHERE id = :id");
        $relativePath = str_replace('../', '', $filename); // Store relative path
        $stmt->bindParam(':path', $relativePath);
        $stmt->bindParam(':id', $quotationId, PDO::PARAM_INT);
        $stmt->execute();
        
        echo json_encode(['success' => true, 'message' => 'Image saved successfully', 'path' => $relativePath]);
    } catch (PDOException $e) {
        // Still consider it a success if file was saved, even if DB update failed
        error_log("Error updating quotation with image path: " . $e->getMessage());
        echo json_encode(['success' => true, 'message' => 'Image saved but database not updated', 'path' => $filename]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to save image']);
}


// Cleans up
imagedestroy($image);
imagedestroy($newImage);
?>
