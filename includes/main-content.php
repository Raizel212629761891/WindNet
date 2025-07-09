<div class="relative z-10 w-full max-w-[90%] mx-auto p-6 pt-8">
        <div class="text-center mb-10 animate__animated animate__fadeIn">
            <h1 class="text-4xl md:text-5xl font-bold mb-2 bg-clip-text text-transparent bg-gradient-to-r from-primary-400 to-secondary-500">Build Your Dream PC</h1>
            <p class="text-gray-300 max-w-3xl mx-auto">Select your components below to create your perfect custom build. We'll help you create a powerful machine that meets your needs.</p>
        </div>

        <!-- PC Builder Main Container -->
        <div class="w-full mx-auto bg-gray-800 bg-opacity-50 backdrop-blur-md rounded-2xl shadow-2xl border border-white border-opacity-10 overflow-hidden animate__animated animate__fadeInUp">
            <!-- PC Builder Body -->
            <div class="flex flex-col md:flex-row">
                <!-- Component Selection (Left Side) -->
                <div class="md:w-4/5 p-6">
                    <h3 class="text-xl font-semibold mb-4 flex items-center">
                        <i data-lucide="cpu" class="w-5 h-5 mr-2 text-primary-400"></i>
                        Select Components
                    </h3>
                    
                    <form id="pcBuilderForm" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php foreach ($categories as $dbCategory => $details): ?>
                            <div class="bg-gray-800 rounded-xl p-5 transition-all duration-300 hover:shadow-lg hover:shadow-primary-900/20 border border-gray-700">
                                <label class="block mb-3 font-semibold text-white flex items-center">
                                    <i data-lucide="<?= $details['icon'] ?>" class="w-5 h-5 mr-2 text-primary-400"></i>
                                    <?= $details['label'] ?>
                                </label>
                                <div class="flex flex-col space-y-4">
                                    <!-- Component Image Display -->
                                    <div class="component-image-container h-48 bg-gray-900 rounded-lg flex items-center justify-center overflow-hidden cursor-pointer relative" 
                                         data-category="<?= strtolower($details['label']) ?>"
                                         data-label="<?= $details['label'] ?>"
                                         data-icon="<?= $details['icon'] ?>"
                                         data-db-category="<?= $dbCategory ?>">
                                        <img 
                                            id="image-<?= strtolower($details['label']) ?>" 
                                            src="../Images/components/placeholder.png" 
                                            alt="<?= $details['label'] ?>" 
                                            class="component-image max-h-full max-w-full object-contain p-2 transition-all duration-300"
                                            onerror="this.src='../Images/components/placeholder.png'"
                                        >
                                        <div class="absolute inset-0 bg-primary-600 bg-opacity-20 flex items-center justify-center opacity-0 hover:opacity-100 transition-opacity duration-300">
                                            <span class="bg-primary-600 text-white px-4 py-2 rounded-lg shadow-lg transform transition-transform duration-300 hover:scale-105">
                                                <i data-lucide="plus" class="w-4 h-4 mr-1 inline-block"></i> Select <?= $details['label'] ?>
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <!-- Component Selection Dropdown -->
                                    <select 
                                        name="<?= strtolower($details['label']) ?>" 
                                        onchange="updateSelection(this); calculateTotal(); updateComponentImage(this); updateComponentPrices(this);" 
                                        class="w-full p-3 rounded-lg bg-gray-900 text-white border border-gray-700 focus:border-primary-500 focus:ring-2 focus:ring-primary-500 transition duration-300 text-sm"
                                        data-category="<?= $details['label'] ?>"
                                    >
                                        <option value="0" data-price="0" data-image="../Images/components/placeholder.png" data-installment="0">-- Select <?= $details['label'] ?> --</option>
                                        <?php
                                        $items = getItems($db, $dbCategory);
                                        foreach ($items as $item):
                                            // Add data-specs attribute for compatibility filtering
                                            $specs = isset($item['Specs']) ? htmlspecialchars($item['Specs']) : '';
                                            
                                            // Get image path or use default placeholder
                                            $imagePath = isset($item['Image']) && !empty($item['Image']) 
                                                ? htmlspecialchars($item['Image']) 
                                                : "Images/components/" . strtolower($dbCategory) . "/default.png";
                                                
                                            // Ensure the path starts with Images/components if it's a relative path
                                            if (!empty($imagePath)) {
                                                if (!strpos($imagePath, 'Images/components/') === 0) {
                                                    $imagePath = "Images/components/" . strtolower($dbCategory) . "/" . basename($imagePath);
                                                }
                                            }
                                        ?>
                                            <option
                                                value="<?= htmlspecialchars($item['Name']) ?>" 
                                                data-price="<?= $item['Cash_Price'] ?>" 
                                                data-installment="<?= $item['Regular_Price'] ?>"
                                                data-specs="<?= $specs ?>"
                                                data-image="<?= $imagePath ?>">
                                                <?= $item['Name'] ?> - ₱<?= number_format($item['Cash_Price'], 2) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    
                                    <!-- Price Display Box -->
                                    <div class="mt-3 bg-gray-800 rounded-lg p-3 border border-gray-700 price-display">
                                        <div class="flex justify-between items-center">
                                            <span class="text-sm text-gray-400">Price:</span>
                                            <span class="text-sm font-medium text-white" id="regular-price-<?= strtolower($details['label']) ?>">₱0.00</span>
                                        </div>
                                        <div class="flex justify-between items-center mt-1">
                                            <span class="text-sm text-gray-400">Discounted Price:</span>
                                            <span class="text-sm font-medium text-primary-400" id="cash-price-<?= strtolower($details['label']) ?>">₱0.00</span>
                                        </div>

                                    </div>
                                    <div class="mt-2 text-sm text-gray-400 hidden selection-details" id="details-<?= strtolower($details['label']) ?>">
                                        <div class="flex items-center">
                                            <i data-lucide="check-circle" class="w-4 h-4 mr-1 text-green-500"></i>
                                            <span class="selected-item">No item selected</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </form>
                </div>