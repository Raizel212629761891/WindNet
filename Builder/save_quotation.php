<?php
// Builder/save_quotation.php

// Enable detailed error logging for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include database connection
require_once 'db_connect.php';

// Set response header to JSON
header('Content-Type: application/json');

// Get the posted JSON data
$jsonData = file_get_contents('php://input');
$data = json_decode($jsonData, true);

// Log received data for debugging
error_log("Received data: " . print_r($data, true));

// Basic validation
if (empty($data['items']) || !isset($data['final_price'])) {
    echo json_encode(['success' => false, 'message' => 'Missing or invalid required quotation data.']);
    exit;
}

// Convert values to appropriate types for database
$finalPrice = (float) $data['final_price'];
$totalDiscount = (float) ($data['total_discount'] ?? 0);

$db = null; // Initialize $db
try {
    $db = getDbConnection();
    $db->beginTransaction();

    // --- Insert into quotation table ---
    $sqlQuotation = "INSERT INTO quotation (customer_name, customer_contact, notes, final_price, total_discount) 
                    VALUES (:name, :contact, :notes, :final_price, :discount)";
    $stmtQuotation = $db->prepare($sqlQuotation);

    $stmtQuotation->bindValue(':name', $data['customer_name'] ?? null);
    $stmtQuotation->bindValue(':contact', $data['customer_contact'] ?? null);
    $stmtQuotation->bindValue(':notes', $data['notes'] ?? null);
    $stmtQuotation->bindValue(':final_price', $finalPrice);
    $stmtQuotation->bindValue(':discount', $totalDiscount);
    $stmtQuotation->execute();

    // Get the ID of the new quotation (this is our Quotation Number)
    $quotationId = $db->lastInsertId();

    // --- Insert into quotation_items table ---
    $sqlItems = "INSERT INTO quotation_items (quotation_id, inventory_id, quantity, price) 
                VALUES (:qid, :inv_id, :qty, :price)";
    $stmtItems = $db->prepare($sqlItems);

    foreach ($data['items'] as $item) {
        // Log each item for debugging
        error_log("Processing item: " . print_r($item, true));
        
        // Validate item structure before using it
        if (!isset($item['inventory_id']) || !isset($item['quantity']) || !isset($item['price'])) {
            throw new Exception("Invalid item data received from frontend.");
        }
        
        // Convert values to appropriate types
        $inventoryId = (int) $item['inventory_id'];
        $quantity = (int) $item['quantity'];
        $price = (float) $item['price'];
        
        $stmtItems->bindValue(':qid', $quotationId, PDO::PARAM_INT);
        $stmtItems->bindValue(':inv_id', $inventoryId, PDO::PARAM_INT);
        $stmtItems->bindValue(':qty', $quantity, PDO::PARAM_INT);
        $stmtItems->bindValue(':price', $price);
        $stmtItems->execute();
    }

    // --- Commit Transaction ---
    $db->commit();

    // --- Return Success ---
    echo json_encode(['success' => true, 'quotation_id' => $quotationId]);

} catch (PDOException $e) {
    // --- Rollback Transaction on error ---
    if ($db && $db->inTransaction()) {
        $db->rollBack();
    }

    // --- Log and Return Error ---
    error_log("Save Quotation DB Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred while saving quotation: ' . $e->getMessage()]);

} catch (Exception $e) { // Catch other potential errors like invalid item data
     // --- Rollback Transaction on error ---
    if ($db && $db->inTransaction()) {
        $db->rollBack();
    }
     // --- Log and Return Error ---
    error_log("Save Quotation General Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An internal error occurred while saving quotation. Details: ' . $e->getMessage()]);

} finally {
    // Close connection explicitly if needed, though PDO usually handles this
    $db = null;
}
?>