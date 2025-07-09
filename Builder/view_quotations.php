<?php
// Include database connection
require_once 'db_connect.php';

// Initialize database connection
try {
    $db = getDbConnection();
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Function to get quotation details by ID
function getQuotationById($db, $id) {
    $stmt = $db->prepare("SELECT * FROM quotation WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Function to get all quotation items for a specific quotation
function getQuotationItems($db, $quotationId) {
    // Join with inventory table to get component details
    $stmt = $db->prepare("
        SELECT qi.*, i.Name, i.Category, i.Cash_Price, i.Regular_Price, i.Image 
        FROM quotation_items qi
        JOIN inventory i ON qi.inventory_id = i.Id
        WHERE qi.quotation_id = :quotation_id
    ");
    $stmt->bindParam(':quotation_id', $quotationId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to get all quotations
function getAllQuotations($db) {
    $stmt = $db->prepare("SELECT * FROM quotation ORDER BY created_at DESC");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Handle quotation deletion if requested
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $quotationId = $_GET['delete'];
    try {
        $db->beginTransaction();
        
        // Delete items first (due to foreign key constraint)
        $stmt = $db->prepare("DELETE FROM quotation_items WHERE quotation_id = :id");
        $stmt->bindParam(':id', $quotationId, PDO::PARAM_INT);
        $stmt->execute();
        
        // Then delete the quotation
        $stmt = $db->prepare("DELETE FROM quotation WHERE id = :id");
        $stmt->bindParam(':id', $quotationId, PDO::PARAM_INT);
        $stmt->execute();
        
        $db->commit();
        
        // Redirect to remove the delete parameter from URL
        header("Location: view_quotations.php?deleted=true");
        exit;
    } catch (PDOException $e) {
        $db->rollBack();
        $error = "Error deleting quotation: " . $e->getMessage();
    }
}

// Get all quotations
$quotations = getAllQuotations($db);

// Format date/time helper function
function formatDateTime($dateString) {
    $date = new DateTime($dateString);
    return $date->format('M j, Y g:i A');
}

// Format price helper function
function formatPrice($price) {
    return '₱' . number_format($price, 2);
}

// View single quotation detail if ID is provided
$viewDetail = false;
$quotationDetail = null;
$quotationItems = null;

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $viewDetail = true;
    $quotationDetail = getQuotationById($db, $_GET['id']);
    
    if ($quotationDetail) {
        $quotationItems = getQuotationItems($db, $_GET['id']);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $viewDetail ? "Quotation #" . $_GET['id'] : "Saved Quotations" ?> - Wind Net</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/lucide@latest/dist/umd/lucide.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            200: '#bae6fd',
                            300: '#7dd3fc',
                            400: '#38bdf8',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                            800: '#075985',
                            900: '#0c4a6e',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-900 text-white font-sans">
    <!-- Animated Gradient Background -->
    <div class="fixed inset-0 bg-gradient-to-br from-primary-700 via-gray-800 to-gray-900 opacity-50 z-0"></div>
    
    <?php include 'navbar.php'; ?>
    
    <!-- Main Content -->
    <div class="relative z-10 w-full max-w-[90%] mx-auto p-6 pt-8">
        <?php if (isset($_GET['deleted']) && $_GET['deleted'] === 'true'): ?>
            <div class="bg-green-600 text-white p-4 mb-6 rounded-lg animate__animated animate__fadeIn">
                <div class="flex items-center">
                    <i data-lucide="check-circle" class="w-5 h-5 mr-2"></i>
                    <span>Quotation deleted successfully.</span>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="bg-red-600 text-white p-4 mb-6 rounded-lg animate__animated animate__fadeIn">
                <div class="flex items-center">
                    <i data-lucide="alert-circle" class="w-5 h-5 mr-2"></i>
                    <span><?= $error ?></span>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="text-center mb-8 animate__animated animate__fadeIn">
            <h1 class="text-4xl md:text-5xl font-bold mb-2 bg-clip-text text-transparent bg-gradient-to-r from-primary-400 to-primary-600">
                <?= $viewDetail ? "Quotation #" . $_GET['id'] : "Saved Quotations" ?>
            </h1>
            <p class="text-gray-300 max-w-3xl mx-auto">
                <?= $viewDetail ? "Details of customer quotation and component selection" : "View all saved customer quotations" ?>
            </p>
        </div>
        
        <div class="bg-gray-800 bg-opacity-50 backdrop-blur-md rounded-2xl shadow-2xl border border-white border-opacity-10 overflow-hidden animate__animated animate__fadeInUp">
            <?php if ($viewDetail && $quotationDetail): ?>
                <!-- Quotation Detail View -->
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <a href="view_quotations.php" class="inline-flex items-center text-primary-400 hover:text-primary-300 transition-colors">
                            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
                            Back to all quotations
                        </a>
                        
                        <div class="flex space-x-3">
                            <a href="#" onclick="window.print()" class="px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white rounded-lg transition-colors flex items-center">
                                <i data-lucide="printer" class="w-4 h-4 mr-2"></i>
                                Print Quotation
                            </a>
                            
                            <a href="view_quotations.php?delete=<?= $quotationDetail['id'] ?>" onclick="return confirm('Are you sure you want to delete this quotation? This action cannot be undone.')" class="px-4 py-2 bg-red-600 hover:bg-red-500 text-white rounded-lg transition-colors flex items-center">
                                <i data-lucide="trash-2" class="w-4 h-4 mr-2"></i>
                                Delete
                            </a>
                        </div>
                    </div>
                    
                    <!-- Customer Information -->
                    <div class="bg-gray-900 rounded-xl p-6 mb-6 border border-gray-700">
                        <h2 class="text-xl font-semibold mb-4 flex items-center">
                            <i data-lucide="user" class="w-5 h-5 mr-2 text-primary-400"></i>
                            Customer Information
                        </h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <p class="text-gray-400 mb-1">Customer Name:</p>
                                <p class="text-lg font-medium"><?= $quotationDetail['customer_name'] ?: 'Not provided' ?></p>
                            </div>
                            
                            <div>
                                <p class="text-gray-400 mb-1">Contact Number:</p>
                                <p class="text-lg font-medium"><?= $quotationDetail['customer_contact'] ?: 'Not provided' ?></p>
                            </div>
                            
                            <div class="md:col-span-2">
                                <p class="text-gray-400 mb-1">Created On:</p>
                                <p class="text-lg font-medium"><?= formatDateTime($quotationDetail['created_at']) ?></p>
                            </div>
                            
                            <?php if ($quotationDetail['notes']): ?>
                                <div class="md:col-span-2">
                                    <p class="text-gray-400 mb-1">Notes:</p>
                                    <p class="bg-gray-800 p-3 rounded-lg border border-gray-700"><?= nl2br(htmlspecialchars($quotationDetail['notes'])) ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Pricing Summary -->
                    <div class="bg-gray-900 rounded-xl p-6 mb-6 border border-gray-700">
                        <h2 class="text-xl font-semibold mb-4 flex items-center">
                            <i data-lucide="dollar-sign" class="w-5 h-5 mr-2 text-primary-400"></i>
                            Pricing Information
                        </h2>
                        
                        <div class="p-5 bg-gray-800 rounded-lg border border-gray-700 shadow-lg max-w-md mx-auto">
                            <div class="flex justify-between items-center mt-2">
                                <p class="text-gray-400">SRP:</p>
                                <p class="text-xl font-bold text-green-400 line-through">
                                    <?= formatPrice($quotationDetail['final_price'] + $quotationDetail['total_discount']) ?>
                                </p>
                            </div>
                            <div class="flex justify-between items-center">
                                <p class="text-gray-400">Bundle Cash Price:</p>
                                <p class="text-2xl font-bold text-primary-400">
                                    <?= formatPrice($quotationDetail['final_price']) ?>
                                </p>
                            </div>
                            <div class="flex justify-between items-center mt-2">
                                <p class="text-gray-400">Total Discount:</p>
                                <p class="text-sm font-medium text-yellow-400">
                                    <?= formatPrice($quotationDetail['total_discount']) ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Component List -->
                    <div class="bg-gray-900 rounded-xl p-6 border border-gray-700">
                        <h2 class="text-xl font-semibold mb-4 flex items-center">
                            <i data-lucide="cpu" class="w-5 h-5 mr-2 text-primary-400"></i>
                            Component List
                        </h2>
                        
                        <?php if (empty($quotationItems)): ?>
                            <div class="text-center py-8 text-gray-400">
                                <i data-lucide="package-x" class="w-12 h-12 mx-auto mb-4 text-gray-600"></i>
                                <p>No components found for this quotation.</p>
                            </div>
                        <?php else: ?>
                            <div class="overflow-x-auto">
                                <table class="w-full border-collapse">
                                    <thead>
                                        <tr class="bg-gray-800 text-left">
                                            <th class="py-3 px-4 font-semibold">Category</th>
                                            <th class="py-3 px-4 font-semibold">Component</th>
                                            <th class="py-3 px-4 font-semibold text-right">Qty</th>
                                            <th class="py-3 px-4 font-semibold text-right">Unit Price</th>
                                            <th class="py-3 px-4 font-semibold text-right">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-700">
                                        <?php foreach ($quotationItems as $item): ?>
                                            <tr class="hover:bg-gray-800 transition-colors">
                                                <td class="py-3 px-4"><?= $item['Category'] ?></td>
                                                <td class="py-3 px-4 font-medium"><?= $item['Name'] ?></td>
                                                <td class="py-3 px-4 text-right"><?= $item['quantity'] ?></td>
                                                <td class="py-3 px-4 text-right"><?= formatPrice($item['price']) ?></td>
                                                <td class="py-3 px-4 text-right font-medium text-primary-400">
                                                    <?= formatPrice($item['price'] * $item['quantity']) ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <!-- Quotation List View -->
                <div class="p-6">
                    <?php if (empty($quotations)): ?>
                        <div class="text-center py-12">
                            <i data-lucide="file-text" class="w-16 h-16 mx-auto text-gray-600 mb-4"></i>
                            <h3 class="text-xl font-semibold mb-2">No Quotations Found</h3>
                            <p class="text-gray-400 mb-6">There are no saved quotations in the system yet.</p>
                            <a href="pc-builder1.php" class="px-6 py-3 bg-primary-600 hover:bg-primary-500 text-white font-semibold rounded-lg transition-colors inline-flex items-center">
                                <i data-lucide="plus" class="w-5 h-5 mr-2"></i>
                                Create New Quotation
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="mb-6 flex justify-between items-center">
                            <h2 class="text-2xl font-semibold">Recent Quotations</h2>
                            <a href="pc-builder1.php" class="px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white font-semibold rounded-lg transition-colors inline-flex items-center">
                                <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
                                New Quotation
                            </a>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="w-full border-collapse">
                                <thead>
                                    <tr class="bg-gray-800 text-left">
                                        <th class="py-3 px-4 font-semibold">ID</th>
                                        <th class="py-3 px-4 font-semibold">Customer</th>
                                        <th class="py-3 px-4 font-semibold">Contact</th>
                                        <th class="py-3 px-4 font-semibold">Date</th>
                                        <th class="py-3 px-4 font-semibold text-right">Total</th>
                                        <th class="py-3 px-4 font-semibold text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-700">
                                    <?php foreach ($quotations as $quotation): ?>
                                        <tr class="hover:bg-gray-800 transition-colors">
                                            <td class="py-3 px-4 font-medium">#<?= $quotation['id'] ?></td>
                                            <td class="py-3 px-4"><?= $quotation['customer_name'] ?: 'Not provided' ?></td>
                                            <td class="py-3 px-4"><?= $quotation['customer_contact'] ?: 'Not provided' ?></td>
                                            <td class="py-3 px-4"><?= formatDateTime($quotation['created_at']) ?></td>
                                            <td class="py-3 px-4 text-right font-medium text-primary-400">
                                                <?= formatPrice($quotation['final_price']) ?>
                                            </td>
                                            <td class="py-3 px-4 text-center">
                                                <div class="flex justify-center space-x-2">
                                                    <a href="view_quotations.php?id=<?= $quotation['id'] ?>" class="p-2 bg-primary-600 hover:bg-primary-500 text-white rounded transition-colors">
                                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                                    </a>
                                                    <a href="view_quotations.php?delete=<?= $quotation['id'] ?>" onclick="return confirm('Are you sure you want to delete this quotation? This action cannot be undone.')" class="p-2 bg-red-600 hover:bg-red-500 text-white rounded transition-colors">
                                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Footer -->
    <?php include 'footer.php'; ?>
    
    <script>
        // Initialize Lucide icons
        lucide.createIcons();
        
        // Print styling
        if (window.matchMedia) {
            const mediaQueryList = window.matchMedia('print');
            mediaQueryList.addEventListener('change', function(mql) {
                if (mql.matches) {
                    document.querySelectorAll('.no-print').forEach(el => {
                        el.style.display = 'none';
                    });
                }
            });
        }
    </script>
</body>
</html>
