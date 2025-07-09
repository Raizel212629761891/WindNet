// Initialize Lucide icons
lucide.createIcons();

// Global variables
var currentCategory = '';
var currentDbCategory = '';
var componentQuantities = {};

// Mapping for motherboard and case form factors
const motherboardFormFactorMap = {
    'b550m': 'matx',
    'b450m': 'matx',
    'h610m': 'matx',
    'prime b660m': 'matx',
    'm-atx': 'matx',
    'matx': 'matx',
    'micro-atx': 'matx',
    'atx': 'atx',
    'z690': 'atx',
    'b550': 'atx',
    'itx': 'itx',
    'mini-itx': 'itx',
    // Add more as needed
};
const caseFormFactorMap = {
    'm-atx': 'matx',
    'matx': 'matx',
    'micro-atx': 'matx',
    'atx': 'atx',
    'itx': 'itx',
    'mini-itx': 'itx',
    // Add more as needed
};

function getFormFactorFromName(name, map) {
    name = (name || '').toLowerCase();
    for (const key in map) {
        if (name.includes(key)) {
            return map[key];
        }
    }
    return null;
}

// Function to open the component selector
function openComponentSelector(category, label, icon, dbCategory) {
    console.log('Opening component selector:', { category, label, icon, dbCategory });
    
    // Set current categories
    currentCategory = category;
    currentDbCategory = dbCategory || category;
    
    // Update modal title
    const modalTitle = document.getElementById('modal-title');
    if (modalTitle) {
        modalTitle.innerHTML = `
            <div class="flex items-center gap-2">
                <i data-lucide="${icon}" class="w-6 h-6 text-blue-400"></i>
                <span>Select ${label}</span>
            </div>
        `;
    }

    // Show the modal
    const modal = document.getElementById('component-selector-modal');
    if (modal) {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
    
    // Show loading state
    const componentGrid = document.getElementById('component-grid');
    if (componentGrid) {
        componentGrid.innerHTML = `
            <div class="col-span-full flex justify-center items-center py-12">
                <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-primary-500"></div>
            </div>
        `;
    }
    
    // Find the dropdown
    let dropdown = document.querySelector(`select[data-category="${label}"]`);
    if (!dropdown) {
        dropdown = document.querySelector(`select[name="${category.toLowerCase()}"]`);
    }
    
    if (dropdown) {
        const options = Array.from(dropdown.options).slice(1);
        const components = options.map(option => ({
            name: option.value,
            cash_price: option.getAttribute('data-price') || '0',
            regular_price: option.getAttribute('data-regular-price') || '0',
            image: option.getAttribute('data-image') || '../Images/components/placeholder.png',
            specs: option.getAttribute('data-specs') || ''
        }));
        
        if (components.length > 0) {
            // Sort components by price (lowest to highest)
            components.sort((a, b) => {
                const priceA = parseFloat(a.cash_price);
                const priceB = parseFloat(b.cash_price);
                return priceA - priceB;
            });
            
            renderComponents(components, category);
        } else {
            componentGrid.innerHTML = `
                <div class="col-span-full text-center py-8">
                    <i data-lucide="package-x" class="w-12 h-12 mx-auto text-gray-600 mb-3"></i>
                    <p class="text-gray-400">No components available</p>
                </div>
            `;
            lucide.createIcons();
        }
    }
    
    // Initialize Lucide icons in the modal
    lucide.createIcons();
}

// Function to close the component selector
function closeComponentSelector() {
    if (window.componentUtils && window.componentUtils.closeComponentSelector) {
        window.componentUtils.closeComponentSelector();
    }
}

// Function to fix image path
function fixImagePath(imagePath, category) {
    if (!imagePath) {
        return `../Images/components/placeholder.png`;
    }
    
    // Get just the filename if it's a full URL
    if (imagePath.includes('http://') || imagePath.includes('https://')) {
        imagePath = imagePath.split('/').pop();
    }
    
    // Clean up the path
    imagePath = imagePath.replace(/^\.\.\//,'').replace(/^Images\//,'').replace(/^components\//,'');
    
    // Map category names to the correct folder names
    const categoryToFolderMap = {
        'cpu': 'processor',
        'memory': 'ram',
        'graphics card': 'gpu',
        'case': 'casing',
        'primary ssd': 'storage',
        'secondary ssd': 'storage',
        'hard drive': 'storage',
        'optional fan': 'fan',
        'cable adapters': 'accessories'
    };
    
    // Get the correct folder name for this category
    const folderName = categoryToFolderMap[category.toLowerCase()] || category.toLowerCase();
    
    // If the image path doesn't already include the folder name, add it
    if (!imagePath.toLowerCase().includes(folderName)) {
        imagePath = `${folderName}/${imagePath}`;
    }
    
    // Check if the image exists
    const fullPath = `../Images/components/${imagePath}`;
    const img = new Image();
    img.onerror = function() {
        console.log(`Image not found: ${fullPath}, using placeholder`);
        return `../Images/components/placeholder.png`;
    };
    img.src = fullPath;
    
    console.log(`Fixed image path for ${category}: ${fullPath}`);
    return fullPath;
}

// Function to render component cards in selector modal
function renderComponents(components, category) {
    const grid = document.getElementById('component-grid');
    if (!grid) return;

    // Get the selected motherboard's form factor if rendering cases
    let formFactor = null;
    if (category.toLowerCase() === 'case') {
        const motherboardSelect = document.querySelector('select[data-category="Motherboard"]');
        if (motherboardSelect) {
            const selectedOption = motherboardSelect.selectedOptions[0];
            const mbName = selectedOption ? selectedOption.text : '';
            const mbSpecs = selectedOption ? (selectedOption.getAttribute('data-specs') || '') : '';
            // Try to get form factor from name, then specs
            formFactor = getFormFactorFromName(mbName, motherboardFormFactorMap) ||
                         getFormFactorFromName(mbSpecs, motherboardFormFactorMap);
        }
    }

    // Filter components if rendering cases and a form factor is selected
    let filteredComponents = components;
    if (category.toLowerCase() === 'case' && formFactor) {
        filteredComponents = components.filter(item => {
            const caseName = (item.name || '').toLowerCase();
            const caseSpecs = (item.specs || '').toLowerCase();
            const caseForm = getFormFactorFromName(caseName, caseFormFactorMap) ||
                             getFormFactorFromName(caseSpecs, caseFormFactorMap);
            return caseForm === formFactor;
        });
    }

    let componentsHtml = '';
    filteredComponents.forEach(item => {
        const imagePath = fixImagePath(item.image, category.toLowerCase());
        const safeName = htmlEscape(item.name);
        const safeCategory = htmlEscape(category);
        const safeSpecs = htmlEscape(item.specs || '');
        componentsHtml += `
            <div class="component-item bg-gray-800/70 rounded-xl overflow-hidden shadow-lg transform hover:scale-105 hover:shadow-blue-400/50 transition-all duration-300 cursor-pointer group" tabindex="0" data-name="${safeName}" data-category="${safeCategory}" data-specs="${safeSpecs}">
                <div class="flex justify-center mt-4">
                    <div class="w-64 h-64 relative flex items-center justify-center">
                        <img src="${imagePath}"
                             alt="${item.name}"
                             class="max-w-full max-h-full object-contain"
                             onerror="this.onerror=null; this.src='../Images/components/placeholder.png';">
                    </div>
                </div>
                <div class="p-5">
                    <h2 class="text-white text-xl font-semibold mb-2 text-center" title="${item.name}">${item.name}</h2>
                    <p class="text-gray-400 text-sm line-through text-center">SRP: ₱${parseFloat(item.regular_price).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</p>
                    <p class="text-blue-400 text-2xl font-bold text-center">₱${parseFloat(item.cash_price).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</p>
                    <div class="flex justify-center mt-4">
                        <span class="px-3 py-1 bg-green-500 text-white text-xs rounded-full font-semibold">In Stock</span>
                    </div>
                </div>
            </div>
        `;
    });

    // If no compatible cases, show a message
    if (category.toLowerCase() === 'case' && formFactor && filteredComponents.length === 0) {
        componentsHtml = `
            <div class="col-span-full text-center py-8">
                <i data-lucide="package-x" class="w-12 h-12 mx-auto text-gray-600 mb-3"></i>
                <p class="text-gray-400">No compatible cases found</p>
            </div>
        `;
    }

    grid.innerHTML = componentsHtml;
    lucide.createIcons();
}

// Function to select a component from the popup
function selectComponent(category, name) {
    console.log(`Selecting component: ${category}, ${name}`);
    
    if (window.componentUtils && window.componentUtils.applyComponentSelection) {
        window.componentUtils.applyComponentSelection(category, name);
        window.componentUtils.showSuccessMessage(category, name);

        // If motherboard is selected, update CPU price to bundle price
        if (category === 'Motherboard') {
            const cpuSelect = document.querySelector('select[data-category="CPU"]');
            if (cpuSelect && cpuSelect.value !== '0') {
                const cpuOption = cpuSelect.options[cpuSelect.selectedIndex];
                const bundleCashPrice = parseFloat(cpuOption.getAttribute('data-bundle-cash-price')) || 0;
                
                if (bundleCashPrice > 0) {
                    // Update the price display for CPU
                    const cpuDetails = document.getElementById('details-cpu');
                    if (cpuDetails) {
                        const priceElement = cpuDetails.querySelector('.text-primary-400');
                        if (priceElement) {
                            priceElement.textContent = `₱${bundleCashPrice.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
                            // Update the data-price attribute to reflect the new price
                            cpuOption.setAttribute('data-price', bundleCashPrice);
                            
                            // Update the main price display
                            const totalPriceElement = document.getElementById('totalPrice');
                            if (totalPriceElement) {
                                // Calculate new total price
                                let totalPrice = bundleCashPrice;
                                const selects = document.querySelectorAll('#pcBuilderForm select');
                                selects.forEach(sel => {
                                    if (sel !== cpuSelect && sel.value !== '0') {
                                        const option = sel.options[sel.selectedIndex];
                                        const price = parseFloat(option.getAttribute('data-price')) || 0;
                                        totalPrice += price;
                                    }
                                });
                                
                                totalPriceElement.textContent = totalPrice.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                            }
                        }
                    }
                }
            }
        }

        // Check if all core components are selected
        const coreComponents = [
            'CPU', 'Motherboard', 'Memory', 'Graphics Card', 
            'CPU Cooler', 'Power Supply', 'Case', 'Monitor', 'Primary SSD'
        ];
        
        const allCoreSelected = coreComponents.every(comp => {
            const select = document.querySelector(`select[data-category="${comp}"]`);
            return select && select.value !== '0';
        });

        console.log('All core components selected:', allCoreSelected);

        // If all core components are selected, update Graphics Card price to bundle price
        if (allCoreSelected) {
            const gpuSelect = document.querySelector('select[data-category="Graphics Card"]');
            if (gpuSelect && gpuSelect.value !== '0') {
                const gpuOption = gpuSelect.options[gpuSelect.selectedIndex];
                const bundleCashPrice = parseFloat(gpuOption.getAttribute('data-bundle-cash-price')) || 0;
                
                console.log('GPU Bundle Cash Price:', bundleCashPrice);
                
                if (bundleCashPrice > 0) {
                    // Update the price display for GPU in the details section
                    const gpuDetails = document.getElementById('details-graphics-card');
                    if (gpuDetails) {
                        const priceElement = gpuDetails.querySelector('.text-primary-400');
                        if (priceElement) {
                            priceElement.textContent = `₱${bundleCashPrice.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
                        }
                    }

                    // Update the price display box
                    const cashPriceElement = document.getElementById('cash-price-graphics card');
                    if (cashPriceElement) {
                        cashPriceElement.textContent = `₱${bundleCashPrice.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
                    }

                    // Update the data-price attribute to reflect the new price
                    gpuOption.setAttribute('data-price', bundleCashPrice);
                    
                    // Update the main price display
                    const totalPriceElement = document.getElementById('totalPrice');
                    if (totalPriceElement) {
                        // Calculate new total price
                        let totalPrice = 0;
                        const selects = document.querySelectorAll('#pcBuilderForm select');
                        selects.forEach(sel => {
                            if (sel.value !== '0') {
                                const option = sel.options[sel.selectedIndex];
                                const price = parseFloat(option.getAttribute('data-price')) || 0;
                                totalPrice += price;
                            }
                        });
                        
                        totalPriceElement.textContent = totalPrice.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    }
                }
            }
        }
    }
    
    // Close the selector
    closeComponentSelector();
    // Update build summary
    updateBuildSummary();
}

// Function to update selection
function updateSelection(selectElement) {
    const category = selectElement.getAttribute('data-category');
    const detailsDiv = document.getElementById(`details-${category.toLowerCase()}`);
    const selectedItem = selectElement.selectedOptions[0].text;
    const value = selectElement.value;
    
    if (detailsDiv) {
        const selectedItemSpan = detailsDiv.querySelector('.selected-item');
        
        if (value !== '0') {
            // Show only the name, not the price
            selectedItemSpan.textContent = selectedItem.split(' - ')[0];
            detailsDiv.classList.remove('hidden');
        } else {
            detailsDiv.classList.add('hidden');
        }
    }

    // Update the build summary section
    const summaryDiv = document.getElementById(`details-${category.toLowerCase()}`);
    if (summaryDiv) {
        if (value !== '0') {
            summaryDiv.classList.remove('hidden');
            const summaryItemSpan = summaryDiv.querySelector('.selected-item');
            if (summaryItemSpan) {
                summaryItemSpan.textContent = selectedItem.split(' - ')[0];
            }
        } else {
            summaryDiv.classList.add('hidden');
        }
    }
}

// Function to update component image
function updateComponentImage(selectElement) {
    if (!selectElement) return;

    const selectedOption = selectElement.options[selectElement.selectedIndex];
    const category = selectElement.getAttribute('data-category').toLowerCase();
    const imageElement = document.getElementById(`image-${category}`);
    
    if (!imageElement) return;

    // Add loading state
    imageElement.classList.add('opacity-0');
    
    // Get and fix image path
    const imagePath = fixImagePath(selectedOption.getAttribute('data-image'), category);
    console.log('Loading image:', imagePath);

    // Create a new image to preload
    const newImage = new Image();
    
    newImage.onload = function() {
        imageElement.src = imagePath;
        setTimeout(() => imageElement.classList.remove('opacity-0'), 50);
    };

    newImage.onerror = function() {
        console.error('Failed to load image:', imagePath);
        imageElement.src = '../Images/components/placeholder.png';
        setTimeout(() => imageElement.classList.remove('opacity-0'), 50);
    };

    newImage.src = imagePath;
}

// Function to update component prices display
function updateComponentPrices(selectElement) {
    const category = selectElement.getAttribute('data-category');
    const selectedOption = selectElement.selectedOptions[0];
    
    // Get all price values
    const regularPrice = parseFloat(selectedOption.getAttribute('data-regular-price')) || 0;
    const srpPrice = parseFloat(selectedOption.getAttribute('data-srp')) || 0;
    let bundlePrice = 0;
    
    // For CPU, use bundle price if motherboard is selected
    if (category === 'CPU') {
        const motherboardSelect = document.querySelector('select[data-category="Motherboard"]');
        const hasMotherboard = motherboardSelect && motherboardSelect.value !== '0';
        if (hasMotherboard) {
            bundlePrice = parseFloat(selectedOption.getAttribute('data-bundle-cash-price')) || 0;
        } else {
            bundlePrice = parseFloat(selectedOption.getAttribute('data-price')) || 0;
        }
    } else {
        bundlePrice = parseFloat(selectedOption.getAttribute('data-price')) || 0;
    }
    
    // Get quantity
    const quantityInput = document.getElementById(`quantity-${category.toLowerCase()}`);
    const quantity = quantityInput ? parseInt(quantityInput.value) || 1 : 1;
    
    // Calculate subtotals
    const regularSubtotal = regularPrice * quantity;
    const srpSubtotal = srpPrice * quantity;
    const bundleSubtotal = bundlePrice * quantity;
    
    // Update price displays
    const regularPriceElement = document.getElementById(`regular-price-${category.toLowerCase()}`);
    const srpPriceElement = document.getElementById(`srp-price-${category.toLowerCase()}`);
    const bundlePriceElement = document.getElementById(`bundle-price-${category.toLowerCase()}`);
    
    if (regularPriceElement) {
        regularPriceElement.textContent = `₱${regularSubtotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
    }
    
    if (srpPriceElement) {
        srpPriceElement.textContent = `₱${srpSubtotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
    }
    
    if (bundlePriceElement) {
        bundlePriceElement.textContent = `₱${bundleSubtotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
    }
    
    // Update build summary
    updateBuildSummary();
}

// Function to update quantity for a component
function updateQuantity(category, change) {
    if (window.componentUtils && window.componentUtils.updateQuantity) {
        window.componentUtils.updateQuantity(category, change);
    }
}

// Function to validate quantity input
function validateQuantity(input) {
    if (window.componentUtils && window.componentUtils.validateQuantity) {
        window.componentUtils.validateQuantity(input);
    }
}

// Function to calculate the total price
function calculateTotal() {
    console.log('Calculating total with quantities');
    let totalRegularPrice = 0;
    let totalSRP = 0;
    let totalBundlePrice = 0;

    // Check if motherboard is selected
    const motherboardSelect = document.querySelector('select[data-category="Motherboard"]');
    const hasMotherboard = motherboardSelect && motherboardSelect.value !== '0';

    const selects = document.querySelectorAll('#pcBuilderForm select');
    
    selects.forEach(sel => {
        const category = sel.getAttribute('data-category').toLowerCase();
        const selectedOption = sel.selectedOptions[0];
        
        // Skip placeholder options
        if (selectedOption.value === '0') return;
        
        // Get all price values
        const regularPrice = parseFloat(selectedOption.getAttribute('data-regular-price')) || 0;
        const srpPrice = parseFloat(selectedOption.getAttribute('data-srp')) || 0;
        let bundlePrice = 0;
        
        // For CPU, use bundle price if motherboard is selected
        if (category === 'cpu' && hasMotherboard) {
            bundlePrice = parseFloat(selectedOption.getAttribute('data-bundle-cash-price')) || 0;
        } else {
            bundlePrice = parseFloat(selectedOption.getAttribute('data-price')) || 0;
        }
        
        // Get quantity for this component
        const quantityInput = document.getElementById(`quantity-${category}`);
        const quantity = quantityInput ? parseInt(quantityInput.value) || 1 : 1;
        
        console.log(`Component: ${category}, Quantity: ${quantity}, Regular Price: ${regularPrice}, SRP: ${srpPrice}, Bundle Price: ${bundlePrice}`);
        
        // Store the quantity
        if (window.componentUtils && window.componentUtils.componentQuantities) {
            window.componentUtils.componentQuantities[category] = quantity;
        }
        
        // Add to totals with quantity
        totalRegularPrice += regularPrice * quantity;
        totalSRP += srpPrice * quantity;
        totalBundlePrice += bundlePrice * quantity;
    });
    
    console.log(`Total Regular Price: ${totalRegularPrice}, Total SRP: ${totalSRP}, Total Bundle Price: ${totalBundlePrice}`);
    
    // Update the price displays
    document.getElementById('regularPrice').textContent = totalRegularPrice.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('srpPrice').textContent = totalSRP.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('bundleCashPrice').textContent = totalBundlePrice.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    
    // Calculate and display discounts
    const srpDiscount = totalSRP - totalBundlePrice;
    const regularPriceDiscount = totalRegularPrice - totalBundlePrice;
    
    document.getElementById('srpDiscount').textContent = srpDiscount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('regularPriceDiscount').textContent = regularPriceDiscount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    
    // Update build summary
    updateBuildSummary();
}

// Update component subtotals based on quantity
function updateComponentSubtotals() {
    console.log('Updating component subtotals');
    const selects = document.querySelectorAll('#pcBuilderForm select');
    
    selects.forEach(sel => {
        const category = sel.getAttribute('data-category').toLowerCase();
        const selectedOption = sel.selectedOptions[0];
        
        if (selectedOption.value !== '0') {
            const cashPrice = parseFloat(selectedOption.getAttribute('data-price')) || 0;
            const srpPrice = parseFloat(selectedOption.getAttribute('data-srp')) || 0;
            
            // Get quantity for this component
            const quantityInput = document.getElementById(`quantity-${category}`);
            const quantity = quantityInput ? parseInt(quantityInput.value) || 1 : 1;
            
            console.log(`Updating subtotal for ${category}: ${cashPrice} x ${quantity} = ${cashPrice * quantity}`);
            
            // Update price displays with quantity
            const regularPriceElement = document.getElementById(`regular-price-${category}`);
            const cashPriceElement = document.getElementById(`cash-price-${category}`);
            
            if (regularPriceElement && cashPriceElement) {
                const totalCashPrice = cashPrice * quantity;
                const totalSrpPrice = srpPrice * quantity;
                
                regularPriceElement.textContent = `₱${totalSrpPrice.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
                cashPriceElement.textContent = `₱${totalCashPrice.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
            }
        }
    });
}

// Function to get category icon
function getCategoryIcon(category) {
    const icons = {
        'CPU': 'cpu',
        'Motherboard': 'circuit-board',
        'Memory': 'memory-stick',
        'Storage': 'hard-drive',
        'Graphics Card': 'monitor',
        'CPU Cooler': 'fan',
        'Power Supply': 'battery-charging',
        'Case': 'box',
        'Monitor': 'monitor'
    };

    return icons[category] || 'cpu';
}

// Function to escape HTML
function htmlEscape(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');
}

// Function to handle processor selection and enable/disable motherboard
function handleProcessorSelection(selectElement) {
    const motherboardSelect = document.querySelector('select[data-category="Motherboard"]');
    if (!motherboardSelect) return;

    if (selectElement.value === '0') {
        // If no processor selected, disable motherboard
        motherboardSelect.disabled = true;
        motherboardSelect.selectedIndex = 0;
        // Update motherboard UI to show it's disabled
        const motherboardDetails = document.getElementById('details-motherboard');
        if (motherboardDetails) {
            motherboardDetails.classList.add('hidden');
        }
    } else {
        // If processor selected, enable motherboard
        motherboardSelect.disabled = false;
    }
}

// Initialize performance analysis on page load
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Lucide icons for the new button
    lucide.createIcons();
    
    // Add event listeners to component selects
    document.querySelectorAll('#pcBuilderForm select').forEach(select => {
        select.addEventListener('change', function() {
            updateSelection(this);
            updateComponentImage(this);
            updateComponentPrices(this);
            calculateTotal();
            updateBuildSummary();

            // Check if this is the processor select
            if (this.getAttribute('data-category') === 'CPU') {
                handleProcessorSelection(this);
            }
        });
    });
    
    // Setup compatibility filtering
    setupCompatibilityFiltering();
    
    // Initialize component images on page load
    document.querySelectorAll('#pcBuilderForm select').forEach(select => {
        // Set placeholder image initially
        updateComponentImage(select);
    });
    
    // Initialize all component prices on page load
    const selects = document.querySelectorAll('#pcBuilderForm select');
    selects.forEach(select => {
        updateComponentPrices(select);
    });
    
    // Initialize component quantities
    document.querySelectorAll('.quantity-input').forEach(input => {
        const category = input.getAttribute('data-category');
        if (window.componentUtils && window.componentUtils.componentQuantities) {
            window.componentUtils.componentQuantities[category] = parseInt(input.value) || 1;
        }
    });
    
    // Add event listeners for quantity changes
    document.querySelectorAll('.quantity-input').forEach(input => {
        input.addEventListener('change', function() {
            validateQuantity(this);
            calculateTotal();
        });
        
        input.addEventListener('input', function() {
            validateQuantity(this);
            calculateTotal();
        });
    });
    
    // Add event listeners for quantity buttons
    document.querySelectorAll('.quantity-button').forEach(button => {
        button.addEventListener('click', function() {
            const category = this.getAttribute('data-category');
            const change = parseInt(this.getAttribute('data-change')) || 0;
            if (window.componentUtils && window.componentUtils.updateQuantity) {
                window.componentUtils.updateQuantity(category, change);
            }
        });
    });
    
    // Calculate initial totals
    calculateTotal();
    
    // Set up Reset button functionality
    const resetButton = document.getElementById('resetComponentsBtn');
    if (resetButton) {
        resetButton.addEventListener('click', function() {
            resetAllComponents();
        });
    }
    
    // Delegate select change to update images in case inline handlers fail
    const form = document.getElementById('pcBuilderForm');
    if (form) {
        form.addEventListener('change', function(e) {
            const sel = e.target;
            if (sel.tagName === 'SELECT' && sel.hasAttribute('data-category')) {
                console.log('Delegated select change:', sel);
                updateComponentImage(sel);
            }
        });
    }

    // Add click handlers to all component image containers
    document.querySelectorAll('.component-image-container').forEach(container => {
        container.addEventListener('click', function(e) {
            const category = this.getAttribute('data-category');
            const label = this.getAttribute('data-label');
            const icon = this.getAttribute('data-icon');
            const dbCategory = this.getAttribute('data-db-category');
            
            if (category && label) {
                openComponentSelector(category, label, icon, dbCategory);
            }
        });
    });
    
    // Add click handler to component grid items
    const componentGrid = document.getElementById('component-grid');
    if (componentGrid) {
        componentGrid.addEventListener('click', function(e) {
        const item = e.target.closest('.component-item');
        if (item) {
            const name = item.getAttribute('data-name');
            const category = item.getAttribute('data-category');
            selectComponent(category, name);
        }
    });
    }
    
    // Add click handler to close modal button
    const closeModalButton = document.querySelector('[data-modal-close]');
    if (closeModalButton) {
        closeModalButton.addEventListener('click', function() {
            closeComponentSelector();
        });
    }
    
    // Add click handler to modal overlay
    const modalOverlay = document.querySelector('.modal-overlay');
    if (modalOverlay) {
        modalOverlay.addEventListener('click', function() {
            closeComponentSelector();
        });
    }

    // Initialize motherboard state based on processor selection
    const processorSelect = document.querySelector('select[data-category="CPU"]');
    if (processorSelect) {
        handleProcessorSelection(processorSelect);
    }
});

// Function to set up compatibility filtering between CPU and motherboard
function setupCompatibilityFiltering() {
    const processorSelect = document.querySelector('select[data-category="CPU"]');
    const motherboardSelect = document.querySelector('select[data-category="Motherboard"]');
    const ramSelect = document.querySelector('select[data-category="Memory"]');
    const caseSelect = document.querySelector('select[data-category="Case"]');
    
    if (!processorSelect || !motherboardSelect || !ramSelect || !caseSelect) return;
    
    // Store original options for motherboard, RAM, and case
    const allMotherboardOptions = Array.from(motherboardSelect.options).map(opt => opt.cloneNode(true));
    const allRamOptions = Array.from(ramSelect.options).map(opt => opt.cloneNode(true));
    const allCaseOptions = Array.from(caseSelect.options).map(opt => opt.cloneNode(true));
    const allCpuOptions = Array.from(processorSelect.options).map(opt => opt.cloneNode(true));
    
    // Listen for changes to motherboard selection
    motherboardSelect.addEventListener('change', function() {
        // Get motherboard specs
        const selectedOption = this.selectedOptions[0];
        const motherboardSpecs = selectedOption.getAttribute('data-specs') || '';
        const motherboardName = selectedOption.text;
        
        console.log('Motherboard Selected:', motherboardName);
        console.log('Motherboard Specs:', motherboardSpecs);
        
        // Extract socket type from motherboard specs
        const socketType = extractSocketType(motherboardSpecs, motherboardName);
        console.log('Detected motherboard socket:', socketType);
        
        if (socketType) {
            // Filter CPUs based on socket compatibility
            filterCpusBySocket(processorSelect, allCpuOptions, socketType);
        } else if (this.value === '0') {
            // Reset CPU options if no motherboard is selected
            resetCpuOptions(processorSelect, allCpuOptions);
        }
        
        // Extract RAM type from motherboard specs
        const ramType = extractRamType(motherboardSpecs, motherboardName);
        console.log('Detected RAM type:', ramType);
        
        // Extract form factor from motherboard specs
        const formFactor = extractFormFactor(motherboardSpecs, motherboardName);
        console.log('Detected form factor:', formFactor);
        
        if (ramType) {
            // Filter RAM options based on compatibility
            filterRamByType(ramSelect, allRamOptions, ramType);
        } else if (this.value === '0') {
            // Reset RAM options if no motherboard is selected
            resetRamOptions(ramSelect, allRamOptions);
        }
        
        if (formFactor) {
            // Filter case options based on form factor
            filterCasesByFormFactor(caseSelect, allCaseOptions, formFactor);
        } else if (this.value === '0') {
            // Reset case options if no motherboard is selected
            resetCaseOptions(caseSelect, allCaseOptions);
        }
    });
    
    // Listen for changes to processor selection
    processorSelect.addEventListener('change', function() {
        // Get CPU specs/socket
        const selectedOption = this.selectedOptions[0];
        const cpuSpecs = selectedOption.getAttribute('data-specs') || '';
        const cpuName = selectedOption.text;
        
        console.log('CPU Selected:', cpuName);
        console.log('CPU Specs:', cpuSpecs);
        
        // Extract socket type from CPU
        const socketType = extractSocketType(cpuSpecs, cpuName);
        console.log('Detected CPU socket:', socketType);
        
        if (socketType) {
            // Filter motherboards based on socket compatibility
            filterMotherboardsBySocket(motherboardSelect, allMotherboardOptions, socketType);
        } else if (this.value === '0') {
            // Reset motherboard options if no CPU is selected
            resetMotherboardOptions(motherboardSelect, allMotherboardOptions);
        }
    });
    
    // Run initial filtering if CPU or motherboard is already selected
    if (processorSelect.value !== '0') {
        processorSelect.dispatchEvent(new Event('change'));
    }
    
    if (motherboardSelect.value !== '0') {
        motherboardSelect.dispatchEvent(new Event('change'));
    }
}

// Extract socket type from CPU specs or name
function extractSocketType(cpuSpecs, cpuName) {
    // First check if socket info is in the specs
    if (cpuSpecs) {
        const specsLower = cpuSpecs.toLowerCase();
        
        console.log('Analyzing CPU specs for socket type:', specsLower);
        
        // Check for common socket types in specs
        if (specsLower.includes('lga1700') || specsLower.includes('lga 1700')) {
            return 'LGA1700';
        }
        if (specsLower.includes('lga1200') || specsLower.includes('lga 1200')) {
            return 'LGA1200';
        }
        if (specsLower.includes('lga1151') || specsLower.includes('lga 1151')) {
            return 'LGA1151';
        }
        if (specsLower.includes('am4')) {
            return 'AM4';
        }
        if (specsLower.includes('am5')) {
            return 'AM5';
        }
    }
    
    // Fall back to analyzing CPU name if specs don't contain socket info
    if (!cpuName) return null;
    
    const cpuNameLower = cpuName.toLowerCase();
    console.log('Analyzing CPU name for socket type:', cpuNameLower);
    
    // Try to detect socket type from CPU name
    if (cpuNameLower.includes('ryzen') || 
        cpuNameLower.includes('amd')) {
        
        // Ryzen 7000 series uses AM5
        if (/7\d{3}/i.test(cpuNameLower) || cpuNameLower.includes('7000')) {
            return 'AM5';
        }
        
        // Most other Ryzen chips use AM4 (3000, 5000, some 4000 series)
        return 'AM4';
    }
    
    // Intel socket detection - improve this section
    if (cpuNameLower.includes('intel') || 
        cpuNameLower.includes('i3') || 
        cpuNameLower.includes('i5') || 
        cpuNameLower.includes('i7') || 
        cpuNameLower.includes('i9') ||
        cpuNameLower.includes('pentium') ||
        cpuNameLower.includes('celeron')) {
        
        console.log('Intel CPU detected, analyzing generation');
        
        // 12th and 13th gen Intel use LGA1700
        if (/1[23][th]|1[23]\d{3}|13th|12th/i.test(cpuNameLower)) {
            console.log('12th/13th gen Intel detected -> LGA1700');
            return 'LGA1700';
        }
        
        // 10th and 11th gen Intel use LGA1200
        if (/1[01][th]|1[01]\d{3}|11th|10th/i.test(cpuNameLower)) {
            console.log('10th/11th gen Intel detected -> LGA1200');
            return 'LGA1200';
        }
        
        // 8th and 9th gen Intel use LGA1151
        if (/[89][th]|[89]\d{3}|9th|8th/i.test(cpuNameLower)) {
            console.log('8th/9th gen Intel detected -> LGA1151');
            return 'LGA1151';
        }
        
        // For any Intel CPU without clear generation marker, default to LGA1200
        console.log('Intel CPU with unspecified generation, defaulting to LGA1200');
        return 'LGA1200';
    }
    
    console.log('Could not determine socket type from CPU name');
    return null;
}

// Extract RAM type from motherboard specs or name
function extractRamType(motherboardSpecs, motherboardName) {
    // First check if RAM type info is in the specs
    if (motherboardSpecs) {
        const specsLower = motherboardSpecs.toLowerCase();
        
        console.log('Analyzing motherboard specs for RAM type:', specsLower);
        
        // Check for common RAM types in specs
        if (specsLower.includes('ddr5')) {
            return 'DDR5';
        }
        if (specsLower.includes('ddr4')) {
            return 'DDR4';
        }
        if (specsLower.includes('ddr3')) {
            return 'DDR3';
        }
    }
    
    // Fall back to analyzing motherboard name if specs don't contain RAM type info
    if (!motherboardName) return null;
    
    const motherboardNameLower = motherboardName.toLowerCase();
    console.log('Analyzing motherboard name for RAM type:', motherboardNameLower);
    
    // Try to detect RAM type from motherboard name
    if (motherboardNameLower.includes('ddr5')) {
        return 'DDR5';
    }
    if (motherboardNameLower.includes('ddr4')) {
        return 'DDR4';
    }
    if (motherboardNameLower.includes('ddr3')) {
        return 'DDR3';
    }
    
    // Use socket type to guess RAM type if available
    if (motherboardSpecs) {
        const specsLower = motherboardSpecs.toLowerCase();
        
        // AM5 and LGA1700 usually use DDR5
        if (specsLower.includes('am5') || specsLower.includes('lga1700') || specsLower.includes('lga 1700')) {
            return 'DDR5';
        }
        
        // AM4, LGA1200, LGA1151 usually use DDR4
        if (specsLower.includes('am4') || 
            specsLower.includes('lga1200') || specsLower.includes('lga 1200') ||
            specsLower.includes('lga1151') || specsLower.includes('lga 1151')) {
            return 'DDR4';
        }
    }
    
    // Default to DDR4 if we can't determine RAM type
    return 'DDR4';
}

// Filter motherboard options by socket type
function filterMotherboardsBySocket(motherboardSelect, allOptions, socketType) {
    console.log('Filtering motherboards for socket:', socketType);
    
    // Store current selection before clearing
    const currentSelectedValue = motherboardSelect.value;
    let currentSelectedOption = null;
    if (currentSelectedValue && currentSelectedValue !== "0") {
        currentSelectedOption = motherboardSelect.options[motherboardSelect.selectedIndex];
    }
    
    // Clear current motherboard options
    while (motherboardSelect.options.length > 0) {
        motherboardSelect.remove(0);
    }
    
    // Add back placeholder option
    const placeholderOption = allOptions.find(opt => !opt.value || opt.value === "0");
    if (placeholderOption) {
        const newPlaceholder = placeholderOption.cloneNode(true);
        newPlaceholder.text = `-- Select ${socketType} Motherboard --`;
        motherboardSelect.add(newPlaceholder);
    }
    
    // Add compatible motherboard options
    let compatibleFound = false;
    let compatibleCount = 0;
    let previousSelectionFound = false;
    let previousSelectionIndex = 0;
    
    // Create a debug list of all motherboards for troubleshooting
    console.log('All available motherboards:');
    allOptions.forEach(option => {
        if (option.value && option.value !== "0") {
            const optionSpecs = option.getAttribute('data-specs') || '';
            const optionText = option.text;
            console.log(`- ${optionText} | Specs: ${optionSpecs}`);
        }
    });
    
    // First determine if we're looking for Intel or AMD
    const isIntel = socketType.includes('LGA');
    const isAMD = socketType.includes('AM4') || socketType.includes('AM5');
    
    console.log(`Socket type is ${isIntel ? 'Intel' : (isAMD ? 'AMD' : 'Unknown')}: ${socketType}`);
    
    allOptions.forEach((option, index) => {
        // Skip the placeholder option (already added)
        if (!option.value || option.value === "0") return;
        
        const optionSpecs = option.getAttribute('data-specs') || '';
        const optionText = option.text.toLowerCase();
        
        let isCompatible = false;
        
        // Check if the motherboard specs or name contains the socket type
        if (optionSpecs.toLowerCase().includes(socketType.toLowerCase()) || 
            optionText.includes(socketType.toLowerCase())) {
            isCompatible = true;
        }
        
        // Additional checks based on Intel vs AMD platform
        if (isIntel) {
            // For Intel CPUs, only show motherboards with Intel chipsets
            if (optionText.includes('z690') || optionText.includes('z790') ||
                optionText.includes('z590') || optionText.includes('z490') ||
                optionText.includes('z390') || optionText.includes('z370') ||
                optionText.includes('b760') || optionText.includes('b660') ||
                optionText.includes('b560') || optionText.includes('b460') ||
                optionText.includes('b365') || optionText.includes('b360') ||
                optionText.includes('h670') || optionText.includes('h770') ||
                optionText.includes('h570') || optionText.includes('h470') ||
                optionText.includes('h370') || optionText.includes('h310') ||
                optionText.includes('h610') || optionText.includes('h510') || 
                optionText.includes('h410')) {
                
                console.log(`- ${option.text} has Intel chipset`);
                isCompatible = true;
            }
            
            // Exclude any AMD motherboards explicitly
            if (optionText.includes('am4') || optionText.includes('am5') ||
                optionText.includes('b450') || optionText.includes('b550') ||
                optionText.includes('b650') || optionText.includes('x570') ||
                optionText.includes('x670') || optionText.includes('a520')) {
                console.log(`- ${option.text} is AMD-specific and incompatible with Intel CPU`);
                isCompatible = false;
            }
        }
        
        if (isAMD) {
            // For AMD CPUs, only show motherboards with AMD chipsets
            if (optionText.includes('b450') || optionText.includes('b550') ||
                optionText.includes('b650') || optionText.includes('x570') ||
                optionText.includes('x670') || optionText.includes('a520')) {
                
                console.log(`- ${option.text} has AMD chipset`);
                isCompatible = true;
            }
            
            // Special handling for AM4 vs AM5
            if (socketType === 'AM4' && (optionText.includes('b650') || optionText.includes('x670'))) {
                console.log(`- ${option.text} is AM5-specific and incompatible with AM4 CPU`);
                isCompatible = false;
            }
            
            if (socketType === 'AM5' && (optionText.includes('b450') || optionText.includes('b550') || 
                optionText.includes('x570') || optionText.includes('a520'))) {
                console.log(`- ${option.text} is AM4-specific and incompatible with AM5 CPU`);
                isCompatible = false;
            }
            
            // Exclude any Intel motherboards explicitly
            if (optionText.includes('lga') || 
                optionText.includes('z690') || optionText.includes('z790') ||
                optionText.includes('z590') || optionText.includes('z490') ||
                optionText.includes('z390') || optionText.includes('z370')) {
                console.log(`- ${option.text} is Intel-specific and incompatible with AMD CPU`);
                isCompatible = false;
            }
        }
        
        console.log(`- Final compatibility: ${isCompatible ? 'COMPATIBLE' : 'NOT COMPATIBLE'}`);
        
        if (isCompatible) {
            compatibleFound = true;
            compatibleCount++;
            const newOption = option.cloneNode(true);
            motherboardSelect.add(newOption);
            
            // Check if this is our previously selected option
            if (currentSelectedOption && currentSelectedOption.value === option.value) {
                previousSelectionFound = true;
                previousSelectionIndex = motherboardSelect.options.length - 1;
            }
        }
    });
    
    console.log(`Found ${compatibleCount} compatible motherboards for socket ${socketType}`);
    
    // If no compatible motherboards found, add all options back
    if (!compatibleFound) {
        console.warn(`No compatible motherboards found for socket: ${socketType}, showing all options`);
        allOptions.forEach(option => {
            if (option.value && option.value !== "0") {
                motherboardSelect.add(option.cloneNode(true));
            }
        });
    }
    
    // Restore previous selection if it was compatible
    if (previousSelectionFound) {
        motherboardSelect.selectedIndex = previousSelectionIndex;
        console.log(`Restored previous motherboard selection: ${motherboardSelect.options[previousSelectionIndex].text}`);
    } else {
        // Only reset to placeholder if we couldn't keep the previous selection
        motherboardSelect.selectedIndex = 0;
    }
}

// Reset motherboard options to original state
function resetMotherboardOptions(motherboardSelect, allOptions) {
    // Clear current options
    while (motherboardSelect.options.length > 0) {
        motherboardSelect.remove(0);
    }
    
    // Add all original options back
    allOptions.forEach(option => {
        motherboardSelect.add(option.cloneNode(true));
    });
    
    // Reset selection
    motherboardSelect.selectedIndex = 0;
    calculateTotal();
    updateBuildSummary();
}

// Filter RAM options by type (DDR3, DDR4, DDR5)
function filterRamByType(ramSelect, allOptions, ramType) {
    // Clear current RAM options
    while (ramSelect.options.length > 0) {
        ramSelect.remove(0);
    }
    
    // Add back placeholder option
    const placeholderOption = allOptions.find(opt => !opt.value || opt.value === "0");
    if (placeholderOption) {
        ramSelect.add(placeholderOption.cloneNode(true));
    }
    
    // Add compatible RAM options
    let compatibleFound = false;
    
    allOptions.forEach(option => {
        // Skip the placeholder option (already added)
        if (!option.value || option.value === "0") return;
        
        const optionSpecs = option.getAttribute('data-specs') || '';
        const optionText = option.text.toLowerCase();
        
        let isCompatible = false;
        
        // Check if the RAM type matches in specs or name
        if (optionSpecs.toLowerCase().includes(ramType.toLowerCase()) || 
            optionText.includes(ramType.toLowerCase())) {
            isCompatible = true;
        }
        
        if (isCompatible) {
            compatibleFound = true;
            ramSelect.add(option.cloneNode(true));
        }
    });
    
    // If no compatible RAM found, add all options back
    if (!compatibleFound) {
        console.warn(`No compatible ${ramType} RAM found, showing all options`);
        allOptions.forEach(option => {
            if (option.value && option.value !== "0") {
                ramSelect.add(option.cloneNode(true));
            }
        });
    }
    
    // Add compatibility notice to placeholder
    const firstOption = ramSelect.options[0];
    if (firstOption) {
        firstOption.text = `-- Select ${ramType} Memory --`;
    }
    
    // If current selection is not compatible, reset to placeholder
    if (ramSelect.selectedIndex > 0) {
        const currentSelection = ramSelect.options[ramSelect.selectedIndex];
        const optionSpecs = currentSelection.getAttribute('data-specs') || '';
        const optionText = currentSelection.text.toLowerCase();
        
        if (!optionSpecs.toLowerCase().includes(ramType.toLowerCase()) && 
            !optionText.includes(ramType.toLowerCase())) {
            ramSelect.selectedIndex = 0;
            // Trigger change event to update prices
            ramSelect.dispatchEvent(new Event('change'));
        }
    }
}

// Reset RAM options to original state
function resetRamOptions(ramSelect, allOptions) {
    // Clear current RAM options
    while (ramSelect.options.length > 0) {
        ramSelect.remove(0);
    }
    
    // Add all original options back
    allOptions.forEach(option => {
        ramSelect.add(option.cloneNode(true));
    });
}

// Extract form factor from motherboard specs
function extractFormFactor(motherboardSpecs, motherboardName) {
    if (!motherboardSpecs && !motherboardName) return null;
    
    // Convert to lowercase for case-insensitive matching
    const specs = motherboardSpecs ? motherboardSpecs.toLowerCase() : '';
    const name = motherboardName ? motherboardName.toLowerCase() : '';
    
    // Check for matx first (either in specs or name ends with 'm')
    if (specs.includes('matx') || name.endsWith('m')) {
        return 'matx';
    }
    // Then check for itx
    else if (specs.includes('itx')) {
        return 'itx';
    }
    // Finally check for atx
    else if (specs.includes('atx')) {
        return 'atx';
    }
    
    return null;
}

// Filter cases based on form factor
function filterCasesByFormFactor(caseSelect, allOptions, formFactor) {
    if (!caseSelect || !allOptions || !formFactor) return;

    caseSelect.innerHTML = '';
    const defaultOption = document.createElement('option');
    defaultOption.value = '0';
    defaultOption.textContent = 'Select Case';
    caseSelect.appendChild(defaultOption);

    allOptions.forEach(option => {
        const caseSpecs = (option.getAttribute('data-specs') || '').toLowerCase();
        // Split by comma, trim, and check for exact match
        const specsArr = caseSpecs.split(',').map(s => s.trim());
        if (specsArr.includes(formFactor)) {
            caseSelect.appendChild(option.cloneNode(true));
        }
    });

    if (caseSelect.options.length === 1) {
        const noCompatibleOption = document.createElement('option');
        noCompatibleOption.value = '0';
        noCompatibleOption.textContent = 'No compatible cases found';
        caseSelect.appendChild(noCompatibleOption);
    }
}

// Reset case options
function resetCaseOptions(caseSelect, allOptions) {
    if (!caseSelect || !allOptions) return;
    
    // Clear current options
    caseSelect.innerHTML = '';
    
    // Add default option
    const defaultOption = document.createElement('option');
    defaultOption.value = '0';
    defaultOption.textContent = 'Select Case';
    caseSelect.appendChild(defaultOption);
    
    // Add all original options
    allOptions.forEach(option => {
        caseSelect.appendChild(option.cloneNode(true));
    });
}

function updatePerformanceAnalysis() {
    const cpu = document.querySelector('select[data-category="CPU"]').value;
    const gpu = document.querySelector('select[data-category="Graphics Card"]').value;
    const ram = document.querySelector('select[data-category="Memory"]').value;
    const storage = document.querySelector('select[data-category="Storage"]').value;
    const analysisDiv = document.getElementById('performanceAnalysis');

    // Show the analysis section
    analysisDiv.classList.remove('hidden');

    // Check if essential components are selected
    if (cpu === '0' || gpu === '0' || ram === '0') {
        resetPerformanceUI();
        return;
    }

    // Calculate performance scores
    const scores = calculatePerformanceScores(cpu, gpu, ram, storage);
    updatePerformanceUI(scores);

    // Calculate bottlenecks
    const bottlenecks = calculateBottlenecks(cpu, gpu, ram);
    updateBottleneckUI(bottlenecks);
}

function resetAllComponents() {
    const selects = document.querySelectorAll('#pcBuilderForm select');
    
    // Reset each select to its first option
    selects.forEach(select => {
        select.selectedIndex = 0;
        
        // Trigger change event to update UI
        const event = new Event('change');
        select.dispatchEvent(event);
    });
    
    // Reset quantities to 1
    document.querySelectorAll('.quantity-input').forEach(input => {
        input.value = 1;
        const category = input.getAttribute('data-category');
        if (window.componentUtils && window.componentUtils.componentQuantities) {
            window.componentUtils.componentQuantities[category] = 1;
        }
    });
    
    // Reset build summary
    const detailsDivs = document.querySelectorAll('[id^="details-"]');
    detailsDivs.forEach(div => {
        div.classList.add('hidden');
    });
    
    // Reset price displays
    document.getElementById('totalPrice').textContent = '0.00';
    document.getElementById('installmentPrice').textContent = '0.00';
    document.getElementById('priceDifference').textContent = '0.00';
    
    // Update message in build summary
    const buildSummary = document.getElementById('buildSummary');
    if (buildSummary) {
        const message = buildSummary.querySelector('p');
        if (message) {
            message.textContent = 'All components have been reset';
            message.classList.add('text-primary-400');
            
            // Revert back to original message after 3 seconds
            setTimeout(() => {
                message.textContent = 'Select components to see your build summary';
                message.classList.remove('text-primary-400');
            }, 3000);
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    var mbSelect = document.querySelector('select[name="motherboard"]');
    if (mbSelect) mbSelect.disabled = false;
});

document.addEventListener('DOMContentLoaded', function() {
    var mbSelect = document.querySelector('select[name="motherboard"]');
    if (mbSelect) mbSelect.disabled = false;
});

function resetComponent(category) {
    // Find the select for this category (use category as-is)
    const select = document.querySelector(`select[data-category="${category}"]`);
    if (select) {
        select.selectedIndex = 0;
        select.dispatchEvent(new Event('change'));
    }
    // Reset quantity to 1
    const quantityInput = document.getElementById(`quantity-${category.toLowerCase()}`);
    if (quantityInput) {
        quantityInput.value = 1;
        if (window.componentUtils && window.componentUtils.componentQuantities) {
            window.componentUtils.componentQuantities[category.toLowerCase()] = 1;
        }
    }
    // Reset price displays for this component
    const regularPriceElement = document.getElementById(`regular-price-${category.toLowerCase()}`);
    const srpPriceElement = document.getElementById(`srp-price-${category.toLowerCase()}`);
    const bundlePriceElement = document.getElementById(`bundle-price-${category.toLowerCase()}`);
    if (regularPriceElement) regularPriceElement.textContent = '₱0.00';
    if (srpPriceElement) srpPriceElement.textContent = '₱0.00';
    if (bundlePriceElement) bundlePriceElement.textContent = '₱0.00';
    // Reset image to placeholder
    const imageElement = document.getElementById(`image-${category.toLowerCase()}`);
    if (imageElement) imageElement.src = '../Images/components/placeholder.png';
    // Hide details
    const detailsDiv = document.getElementById(`details-${category.toLowerCase()}`);
    if (detailsDiv) detailsDiv.classList.add('hidden');
    // Update total and summary
    calculateTotal();
    updateBuildSummary && updateBuildSummary();
}

function updateBuildSummary() {
    const summaryElement = document.getElementById('buildSummary');
    if (!summaryElement) return;
    
    const selects = document.querySelectorAll('#pcBuilderForm select');
    let selectedCount = 0;
    let summaryHTML = '';
    
    selects.forEach(sel => {
        const selectedOption = sel.selectedOptions[0];
        if (selectedOption.value === '0') return; // Skip placeholder options
        
        const category = sel.getAttribute('data-category');
        const dbCategory = sel.getAttribute('data-db-category');
        
        // Get all price values
        const srpPrice = parseFloat(selectedOption.getAttribute('data-srp')) || 0;
        const regularPrice = parseFloat(selectedOption.getAttribute('data-regular-price')) || 0;
        const bundlePrice = parseFloat(selectedOption.getAttribute('data-bundle-cash-price')) || 0;
        
        // Get quantity
        const quantityInput = document.getElementById(`quantity-${category.toLowerCase()}`);
        const quantity = quantityInput ? parseInt(quantityInput.value) || 1 : 1;
        
        // Calculate subtotals
        const srpSubtotal = srpPrice * quantity;
        const regularSubtotal = regularPrice * quantity;
        const bundleSubtotal = bundlePrice * quantity;
        
        selectedCount++;
        
        // Add to summary HTML
        summaryHTML += `
            <div class="flex justify-between items-start py-2 border-b border-gray-700 last:border-0">
                <div class="flex-1">
                    <p class="font-medium">${category}</p>
                    <p class="text-sm text-gray-400">${selectedOption.value}</p>
                    ${quantity > 1 ? `<p class="text-xs text-primary-400">Quantity: ${quantity}</p>` : ''}
                </div>
                <div class="text-right">
                    <p class="text-xs text-gray-400">SRP: <span class="font-medium">₱${srpSubtotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span></p>
                    <p class="text-xs text-blue-400">Regular: <span class="font-medium">₱${regularSubtotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span></p>
                    <p class="text-xs text-primary-400">Bundle: <span class="font-medium">₱${bundleSubtotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span></p>
                </div>
            </div>
        `;
    });
    
    // If no components selected, show placeholder message
    if (selectedCount === 0) {
        summaryHTML = '<p class="text-gray-400 text-center">Select components to see your build summary</p>';
    }
    
    // Update the summary element
    summaryElement.innerHTML = summaryHTML;
}

// Function to filter CPUs based on motherboard socket
function filterCpusBySocket(cpuSelect, allOptions, socketType) {
    console.log('Filtering CPUs for socket:', socketType);
    
    // Store current selection before clearing
    const currentSelectedValue = cpuSelect.value;
    let currentSelectedOption = null;
    if (currentSelectedValue && currentSelectedValue !== "0") {
        currentSelectedOption = cpuSelect.options[cpuSelect.selectedIndex];
    }
    
    // Clear current CPU options
    while (cpuSelect.options.length > 0) {
        cpuSelect.remove(0);
    }
    
    // Add back placeholder option
    const placeholderOption = allOptions.find(opt => !opt.value || opt.value === "0");
    if (placeholderOption) {
        const newPlaceholder = placeholderOption.cloneNode(true);
        newPlaceholder.text = `-- Select ${socketType} CPU --`;
        cpuSelect.add(newPlaceholder);
    }
    
    // Add compatible CPU options
    let compatibleFound = false;
    let compatibleCount = 0;
    let previousSelectionFound = false;
    let previousSelectionIndex = 0;
    
    allOptions.forEach((option, index) => {
        // Skip the placeholder option (already added)
        if (!option.value || option.value === "0") return;
        
        const optionSpecs = option.getAttribute('data-specs') || '';
        const optionText = option.text.toLowerCase();
        
        let isCompatible = false;
        
        // Check if CPU specs or name contains the socket type
        if (optionSpecs.toLowerCase().includes(socketType.toLowerCase()) || 
            optionText.includes(socketType.toLowerCase())) {
            isCompatible = true;
        }
        
        // Additional checks based on socket type
        if (socketType === 'AM4') {
            // AM4 CPUs
            if (optionText.includes('ryzen') && 
                (optionText.includes('3000') || 
                 optionText.includes('4000') || 
                 optionText.includes('5000'))) {
                isCompatible = true;
            }
        } else if (socketType === 'AM5') {
            // AM5 CPUs
            if (optionText.includes('ryzen') && 
                (optionText.includes('7000') || 
                 optionText.includes('7xxx'))) {
                isCompatible = true;
            }
        } else if (socketType === 'LGA1700') {
            // LGA1700 CPUs
            if (optionText.includes('intel') && 
                (optionText.includes('12th') || 
                 optionText.includes('13th') || 
                 optionText.includes('12000') || 
                 optionText.includes('13000'))) {
                isCompatible = true;
            }
        } else if (socketType === 'LGA1200') {
            // LGA1200 CPUs
            if (optionText.includes('intel') && 
                (optionText.includes('10th') || 
                 optionText.includes('11th') || 
                 optionText.includes('10000') || 
                 optionText.includes('11000'))) {
                isCompatible = true;
            }
        }
        
        if (isCompatible) {
            compatibleFound = true;
            compatibleCount++;
            const newOption = option.cloneNode(true);
            cpuSelect.add(newOption);
            
            // Check if this is our previously selected option
            if (currentSelectedOption && currentSelectedOption.value === option.value) {
                previousSelectionFound = true;
                previousSelectionIndex = cpuSelect.options.length - 1;
            }
        }
    });
    
    console.log(`Found ${compatibleCount} compatible CPUs for socket ${socketType}`);
    
    // If no compatible CPUs found, add all options back
    if (!compatibleFound) {
        console.warn(`No compatible CPUs found for socket: ${socketType}, showing all options`);
        allOptions.forEach(option => {
            if (option.value && option.value !== "0") {
                cpuSelect.add(option.cloneNode(true));
            }
        });
    }
    
    // Restore previous selection if it was compatible
    if (previousSelectionFound) {
        cpuSelect.selectedIndex = previousSelectionIndex;
        console.log(`Restored previous CPU selection: ${cpuSelect.options[previousSelectionIndex].text}`);
    } else {
        // Only reset to placeholder if we couldn't keep the previous selection
        cpuSelect.selectedIndex = 0;
    }
}

// Reset CPU options to original state
function resetCpuOptions(cpuSelect, allOptions) {
    // Clear current options
    while (cpuSelect.options.length > 0) {
        cpuSelect.remove(0);
    }
    
    // Add all original options back
    allOptions.forEach(option => {
        cpuSelect.add(option.cloneNode(true));
    });
    
    // Reset selection
    cpuSelect.selectedIndex = 0;
    calculateTotal();
    updateBuildSummary();
}