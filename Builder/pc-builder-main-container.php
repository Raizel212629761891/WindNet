<!-- PC Builder Main Container -->
        <div class="w-full max-w-none bg-gray-800 bg-opacity-50 backdrop-blur-md rounded-2xl shadow-2xl border border-white border-opacity-10 overflow-hidden animate__animated animate__fadeInUp">
            <!-- PC Builder Body -->
            <div class="flex flex-col md:flex-row">
                <!-- Component Selection (Left Side) -->
                <div class="flex-1 p-6 overflow-y-auto max-h-screen">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xl font-semibold flex items-center">
                            <i data-lucide="cpu" class="w-5 h-5 mr-2 text-primary-400"></i>
                            Select Components
                        </h3>
                        <button id="resetComponentsBtn" class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white text-sm font-semibold rounded-lg transition duration-300 flex items-center">
                            <i data-lucide="refresh-cw" class="w-4 h-4 mr-2"></i>
                            Reset All
                        </button>
                    </div>
                    
                    <form id="pcBuilderForm" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <!-- Core Components Section -->
                        <div class="col-span-full mb-2">
                            <h4 class="text-lg font-semibold text-primary-400 border-b border-gray-700 pb-2 mb-4">Core Components</h4>
                        </div>
                        <?php 
                        // Track which categories we've already displayed
                        $coreComponents = array_slice($categories, 0, 9); // First 9 are core components
                        $peripheralsComponents = array_slice($categories, 9); // The rest are peripherals
                        
                        // Display core components first
                        foreach ($coreComponents as $dbCategory => $details): 
                        ?>
                            <div class="bg-gray-800 rounded-xl p-5 transition-all duration-300 hover:shadow-lg hover:shadow-primary-900/20 border border-gray-700 relative">
                                <!-- X Button for Reset -->
                                <button type="button" onclick="resetComponent('<?= $details['label'] ?>')"
                                    class="absolute top-3 right-3 z-10 bg-gray-700 hover:bg-red-600 text-white rounded-full p-1 focus:outline-none focus:ring-2 focus:ring-red-400"
                                    title="Reset this component">
                                    <i data-lucide="x" class="w-4 h-4"></i>
                                </button>
                                <label class="block mb-3 font-semibold text-white flex items-center">
                                    <i data-lucide="<?= $details['icon'] ?>" class="w-5 h-5 mr-2 text-primary-400"></i>
                                    <?= $details['label'] ?>
                                </label>
                                <div class="flex flex-col space-y-4">
                                    <!-- Component Image Display (Clickable) -->
                                    <div class="component-image-container h-48 bg-gray-900 rounded-lg flex items-center justify-center overflow-hidden cursor-pointer relative fancy-image-preview" 
                                         data-category="<?= strtolower($details['label']) ?>"
                                         data-label="<?= $details['label'] ?>"
                                         data-icon="<?= $details['icon'] ?>"
                                         data-db-category="<?= $dbCategory ?>">
                                        <img 
                                            id="image-<?= strtolower($details['label']) ?>" 
                                            src="../Images/components/placeholder.png" 
                                            alt="<?= $details['label'] ?>" 
                                            class="component-image"
                                            style="width:80%; height:80%; object-fit:contain; display:block; margin:auto; border-radius: 12px; box-shadow: 0 4px 16px rgba(56,189,248,0.15); border: 2px solid #0284c7; background: #181f2a;"
                                        >
                                        <div class="absolute inset-0 bg-primary-600 bg-opacity-20 flex items-center justify-center opacity-0 hover:opacity-100 transition-opacity duration-300">
                                            <span class="text-white bg-primary-600 px-3 py-1 rounded-full text-sm font-medium shadow-lg">
                                                <i data-lucide="search" class="w-4 h-4 inline mr-1"></i> Select <?= $details['label'] ?>
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <!-- Component Selection Dropdown -->
                                    <select 
                                        name="<?= strtolower($details['label']) ?>" 
                                        onchange="updateSelection(this); calculateTotal(); updateComponentImage(this); updateComponentPrices(this);" 
                                        class="w-full p-3 rounded-lg bg-gray-900 text-white border border-gray-700 focus:border-primary-500 focus:ring-2 focus:ring-primary-500 transition duration-300 text-sm"
                                        data-category="<?= $details['label'] ?>"
                                        data-db-category="<?= $dbCategory ?>"
                                    >
                                        <option value="0" data-price="0" data-image="http://localhost/Wind%20Net/Images/components/placeholder.png" data-installment="0">-- Select <?= $details['label'] ?> --</option>
                                        <?php
                                        $items = getItems($db, $dbCategory);
                                        foreach ($items as $item):
                                            // Add data-specs attribute for compatibility filtering
                                            $specs = isset($item['Specs']) ? htmlspecialchars($item['Specs']) : '';
                                            
                                            // Determine raw image path or default
                                            if (!empty($item['Image'])) {
                                                $rawPath = $item['Image'];
                                            } else {
                                                $rawPath = "Images/components/" . strtolower($dbCategory) . "/default.png";
                                            }
                                            
                                            // Normalize to absolute URL
                                            if (!preg_match('#^https?://#', $rawPath)) {
                                                $cleanPath = preg_replace('#^(\.\./|\./)#', '', $rawPath);
                                                $baseUrl = "http://" . $_SERVER['HTTP_HOST'];
                                                $basePath = str_replace('\\', '/', dirname(dirname($_SERVER['PHP_SELF'])));
                                                $imagePath = $baseUrl . $basePath . "/" . $cleanPath;
                                            } else {
                                                $imagePath = $rawPath;
                                            }
                                        ?>
                                            <option
                                                value="<?= htmlspecialchars($item['Name']) ?>" 
                                                data-price="<?= $item['Cash_Price'] ?>" 
                                                data-installment="<?= $item['Regular_Price'] ?>"
                                                data-bundle-cash-price="<?= isset($item['Bundle_Cash_Price']) ? $item['Bundle_Cash_Price'] : $item['Cash_Price'] ?>"
                                                data-regular-price="<?= isset($item['Regular_Price']) ? $item['Regular_Price'] : $item['Cash_Price'] ?>"
                                                data-srp="<?= isset($item['SRP']) ? $item['SRP'] : $item['Regular_Price'] ?>"
                                                data-specs="<?= $specs ?>"
                                                data-inventory-id="<?= $item['Id'] ?>"
                                                data-image="<?= $imagePath ?>"
                                                data-watts="<?= isset($item['Watts']) ? (int)$item['Watts'] : 0 ?>">
                                                <?= $item['Name'] ?> - ₱<?= number_format($item['Cash_Price'], 2) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    
                                    <!-- Price Display Box -->
                                    <div class="mt-3 bg-gray-800 rounded-lg p-3 border border-gray-700 price-display">
                                        <div class="flex justify-between items-center">
                                            <span class="text-sm text-gray-400">SRP:</span>
                                            <span class="text-sm font-medium text-green-400" id="srp-price-<?= strtolower($details['label']) ?>">₱0.00</span>
                                        </div>
                                        <div class="flex justify-between items-center mt-1">
                                            <span class="text-sm text-gray-400">Regular Price:</span>
                                            <span class="text-sm font-medium text-blue-400" id="regular-price-<?= strtolower($details['label']) ?>">₱0.00</span>
                                        </div>
                                        <div class="flex justify-between items-center mt-1">
                                            <span class="text-sm text-gray-400">Bundle Cash Price:</span>
                                            <span class="text-sm font-medium text-primary-400" id="bundle-price-<?= strtolower($details['label']) ?>">₱0.00</span>
                                        </div>
                                        
                                        <!-- Quantity Control -->
                                        <div class="flex justify-between items-center mt-3 pt-2 border-t border-gray-700">
                                            <span class="text-sm text-gray-400">Quantity:</span>
                                            <div class="flex items-center space-x-2">
                                                <button type="button" 
                                                        class="w-6 h-6 rounded bg-gray-700 hover:bg-gray-600 flex items-center justify-center focus:outline-none focus:ring-1 focus:ring-primary-500 quantity-decrease"
                                                        data-category="<?= strtolower($details['label']) ?>"
                                                        onclick="updateQuantity('<?= strtolower($details['label']) ?>', -1)">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                                </button>
                                                <input type="number" 
                                                       class="w-10 h-6 bg-gray-900 border border-gray-700 rounded text-center text-white text-sm focus:outline-none focus:ring-1 focus:ring-primary-500 quantity-input"
                                                       id="quantity-<?= strtolower($details['label']) ?>"
                                                       data-category="<?= strtolower($details['label']) ?>"
                                                       value="1"
                                                       min="1"
                                                       max="99"
                                                       onchange="validateQuantity(this); calculateTotal();"
                                                       onkeyup="validateQuantity(this); calculateTotal();">
                                                <button type="button" 
                                                        class="w-6 h-6 rounded bg-gray-700 hover:bg-gray-600 flex items-center justify-center focus:outline-none focus:ring-1 focus:ring-primary-500 quantity-increase"
                                                        data-category="<?= strtolower($details['label']) ?>"
                                                        onclick="updateQuantity('<?= strtolower($details['label']) ?>', 1)">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                                </button>
                                            </div>
                                        </div>
                                        

                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <!-- Peripherals Section -->
                        <div class="col-span-full mb-2 mt-6">
                            <h4 class="text-lg font-semibold text-primary-400 border-b border-gray-700 pb-2 mb-4">Peripherals & Accessories</h4>
                        </div>
                        
                        <?php foreach ($peripheralsComponents as $dbCategory => $details): ?>
                            <div class="bg-gray-800 rounded-xl p-5 transition-all duration-300 hover:shadow-lg hover:shadow-primary-900/20 border border-gray-700 relative">
                                <!-- X Button for Reset -->
                                <button type="button" onclick="resetComponent('<?= $details['label'] ?>')"
                                    class="absolute top-3 right-3 z-10 bg-gray-700 hover:bg-red-600 text-white rounded-full p-1 focus:outline-none focus:ring-2 focus:ring-red-400"
                                    title="Reset this component">
                                    <i data-lucide="x" class="w-4 h-4"></i>
                                </button>
                                <label class="block mb-3 font-semibold text-white flex items-center">
                                    <i data-lucide="<?= $details['icon'] ?>" class="w-5 h-5 mr-2 text-primary-400"></i>
                                    <?= $details['label'] ?>
                                </label>
                                <div class="flex flex-col space-y-4">
                                    <!-- Component Image Display (Clickable) -->
                                    <div class="component-image-container h-48 bg-gray-900 rounded-lg flex items-center justify-center overflow-hidden cursor-pointer relative fancy-image-preview" 
                                         data-category="<?= strtolower($details['label']) ?>"
                                         data-label="<?= $details['label'] ?>"
                                         data-icon="<?= $details['icon'] ?>"
                                         data-db-category="<?= $dbCategory ?>">
                                        <img 
                                            id="image-<?= strtolower($details['label']) ?>" 
                                            src="../Images/components/placeholder.png" 
                                            alt="<?= $details['label'] ?>" 
                                            class="component-image"
                                            style="width:80%; height:80%; object-fit:contain; display:block; margin:auto; border-radius: 12px; box-shadow: 0 4px 16px rgba(56,189,248,0.15); border: 2px solid #0284c7; background: #181f2a;"
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
                                        <option value="0" data-price="0" data-image="http://localhost/Wind%20Net/Images/components/placeholder.png" data-installment="0">-- Select <?= $details['label'] ?> --</option>
                                        <?php
                                        $items = getItems($db, $dbCategory);
                                        foreach ($items as $item):
                                            // Add data-specs attribute for compatibility filtering
                                            $specs = isset($item['Specs']) ? htmlspecialchars($item['Specs']) : '';
                                            
                                            // Determine raw image path or default
                                            if (!empty($item['Image'])) {
                                                $rawPath = $item['Image'];
                                            } else {
                                                $rawPath = "Images/components/" . strtolower($dbCategory) . "/default.png";
                                            }
                                            
                                            // Normalize to absolute URL
                                            if (!preg_match('#^https?://#', $rawPath)) {
                                                $cleanPath = preg_replace('#^(\.\./|\./)#', '', $rawPath);
                                                $baseUrl = "http://" . $_SERVER['HTTP_HOST'];
                                                $basePath = str_replace('\\', '/', dirname(dirname($_SERVER['PHP_SELF'])));
                                                $imagePath = $baseUrl . $basePath . "/" . $cleanPath;
                                            } else {
                                                $imagePath = $rawPath;
                                            }
                                        ?>
                                            <option
                                                value="<?= htmlspecialchars($item['Name']) ?>" 
                                                data-price="<?= $item['Cash_Price'] ?>" 
                                                data-installment="<?= $item['Regular_Price'] ?>"
                                                data-bundle-cash-price="<?= isset($item['Bundle_Cash_Price']) ? $item['Bundle_Cash_Price'] : $item['Cash_Price'] ?>"
                                                data-regular-price="<?= isset($item['Regular_Price']) ? $item['Regular_Price'] : $item['Cash_Price'] ?>"
                                                data-srp="<?= isset($item['SRP']) ? $item['SRP'] : $item['Regular_Price'] ?>"
                                                data-specs="<?= $specs ?>"
                                                data-inventory-id="<?= $item['Id'] ?>"
                                                data-image="<?= $imagePath ?>"
                                                data-watts="<?= isset($item['Watts']) ? (int)$item['Watts'] : 0 ?>">
                                                <?= $item['Name'] ?> - ₱<?= number_format($item['Cash_Price'], 2) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    
                                    <!-- Price Display Box -->
                                    <div class="mt-3 bg-gray-800 rounded-lg p-3 border border-gray-700 price-display">
                                        <div class="flex justify-between items-center">
                                            <span class="text-sm text-gray-400">SRP:</span>
                                            <span class="text-sm font-medium text-green-400" id="srp-price-<?= strtolower($details['label']) ?>">₱0.00</span>
                                        </div>
                                        <div class="flex justify-between items-center mt-1">
                                            <span class="text-sm text-gray-400">Regular Price:</span>
                                            <span class="text-sm font-medium text-blue-400" id="regular-price-<?= strtolower($details['label']) ?>">₱0.00</span>
                                        </div>
                                        <div class="flex justify-between items-center mt-1">
                                            <span class="text-sm text-gray-400">Bundle Cash Price:</span>
                                            <span class="text-sm font-medium text-primary-400" id="bundle-price-<?= strtolower($details['label']) ?>">₱0.00</span>
                                        </div>
                                        
                                        <!-- Quantity Control -->
                                        <div class="flex justify-between items-center mt-3 pt-2 border-t border-gray-700">
                                            <span class="text-sm text-gray-400">Quantity:</span>
                                            <div class="flex items-center space-x-2">
                                                <button type="button" 
                                                        class="w-6 h-6 rounded bg-gray-700 hover:bg-gray-600 flex items-center justify-center focus:outline-none focus:ring-1 focus:ring-primary-500 quantity-decrease"
                                                        data-category="<?= strtolower($details['label']) ?>"
                                                        onclick="updateQuantity('<?= strtolower($details['label']) ?>', -1)">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                                </button>
                                                <input type="number" 
                                                       id="quantity-<?= strtolower($details['label']) ?>" 
                                                       class="w-10 h-6 bg-gray-900 border border-gray-700 rounded text-center text-white text-sm focus:outline-none focus:ring-1 focus:ring-primary-500" 
                                                       value="1" min="1" max="99"
                                                       onchange="validateQuantity(this); calculateTotal();"
                                                       data-category="<?= strtolower($details['label']) ?>">
                                                <button type="button" 
                                                        class="w-6 h-6 rounded bg-gray-700 hover:bg-gray-600 flex items-center justify-center focus:outline-none focus:ring-1 focus:ring-primary-500 quantity-increase"
                                                        data-category="<?= strtolower($details['label']) ?>"
                                                        onclick="updateQuantity('<?= strtolower($details['label']) ?>', 1)">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                                </button>
                                            </div>
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
                