<!-- Summary Section (Middle) -->
                <div class="md:w-1/4 bg-gray-900 p-6 border-l border-gray-700">
                    <!-- Top Section: Build Summary -->
                    <div class="mb-6">
                        <h3 class="text-xl font-semibold mb-4 flex items-center">
                            <i data-lucide="clipboard-list" class="w-5 h-5 mr-2 text-primary-400"></i>
                            Build Summary
                        </h3>
                        
                        <div class="space-y-3 mb-4" id="buildSummary">
                            <!-- This will be populated via JavaScript -->
                            <p class="text-gray-400 text-center">Select components to see your build summary</p>
                        </div>
                        
                        <div class="p-5 bg-gray-800 rounded-lg border border-gray-700 shadow-lg">
                        <div class="flex justify-between items-center mb-2" id="selectedPaymentMethodRow">
                            <p class="text-gray-400 font-semibold">Selected Payment Method:</p>
                            <p class="text-base font-bold text-primary-400" id="selectedPaymentMethodText"></p>
                        </div>
                            <!-- SRP -->
                            <div class="flex justify-between items-center mb-2" id="srpPriceRow">
                                <p class="text-gray-400">SRP:</p>
                                <p class="text-xl font-bold text-green-400">₱<span id="srpPrice">0.00</span></p>
                            </div>
                            
                            <!-- Regular Price -->
                            <div class="flex justify-between items-center mb-2" id="regularPriceRow">
                                <p class="text-gray-400">Regular Price:</p>
                                <p class="text-xl font-bold text-blue-400">₱<span id="regularPrice">0.00</span></p>
                            </div>
                            
                            <!-- Bundle Cash Price -->
                            <div class="flex justify-between items-center mb-2" id="bundleCashPriceRow">
                                <p class="text-gray-400">Bundle Cash Price:</p>
                                <p class="text-2xl font-bold text-primary-400">₱<span id="bundleCashPrice">0.00</span></p>
                            </div>
                            
                            <!-- Total Wattage -->
                            <div class="flex justify-between items-center mb-2 mt-4" id="totalWattageRow">
                                <p class="text-gray-400 font-semibold">Total Wattage:</p>
                                <p class="text-2xl font-bold text-yellow-400"><span id="totalWattage">0</span> W</p>
                            </div>
                            
                            <!-- Discounts -->
                            <div class="mt-4 pt-4 border-t border-gray-700">
                                <div class="flex justify-between items-center mb-2" id="srpDiscountRow">
                                    <p class="text-gray-400">Discount from SRP:</p>
                                    <p class="text-sm font-medium text-yellow-400">₱<span id="srpDiscount">0.00</span></p>
                                </div>
                                <div class="flex justify-between items-center" id="regularPriceDiscountRow">
                                    <p class="text-gray-400">Discount from Regular Price:</p>
                                    <p class="text-sm font-medium text-yellow-400">₱<span id="regularPriceDiscount">0.00</span></p>
                                </div>
                            </div>
                            
                            <!-- Notes -->
                            <div class="mt-4 pt-4 border-t border-gray-700">
                                <div class="text-xs text-gray-400 space-y-1">
                                    <p class="flex items-center">
                                        <i data-lucide="info" class="w-4 h-4 mr-1 text-blue-400"></i>
                                        <span>SRP applies to Home Credit financing</span>
                                    </p>
                                    <p class="flex items-center">
                                        <i data-lucide="credit-card" class="w-4 h-4 mr-1 text-blue-400"></i>
                                        <span>Regular Price applies to CC Straight, BDO/BPI Regular Installment, and BDO 0%</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    
<script> 
function updateBuildSummary() {
    const selects = document.querySelectorAll('#pcBuilderForm select');
    const summary = document.getElementById('buildSummary');
    let selectedCount = 0;
    let summaryHTML = '';
    let totalRegularPrice = 0;
    let totalSRP = 0;
    let totalBundlePrice = 0;
    let totalWattage = 0;

    // Check if motherboard is selected
    const motherboardSelect = document.querySelector('select[data-category="Motherboard"]');
    const hasMotherboard = motherboardSelect && motherboardSelect.value !== '0';

    selects.forEach(sel => {
        const category = sel.getAttribute('data-category');
        const selectedOption = sel.selectedOptions[0];
        const value = sel.value;

        if (value !== '0') {
            selectedCount++;
            const itemName = selectedOption.text.split(' - ')[0];
            
            // Get all price values
            const regularPrice = parseFloat(selectedOption.getAttribute('data-regular-price')) || 0;
            const srpPrice = parseFloat(selectedOption.getAttribute('data-srp')) || 0;
            let bundlePrice = 0;
            
            // For CPU, use bundle price if motherboard is selected
            if (category === 'CPU' && hasMotherboard) {
                bundlePrice = parseFloat(selectedOption.getAttribute('data-bundle-cash-price')) || 0;
            } else {
                bundlePrice = parseFloat(selectedOption.getAttribute('data-price')) || 0;
            }
            // Get quantity
            const quantityInput = document.getElementById(`quantity-${category.toLowerCase()}`);
            const quantity = quantityInput ? parseInt(quantityInput.value) || 1 : 1;
            // Accumulate totals (multiply by quantity)
            totalRegularPrice += regularPrice * quantity;
            totalSRP += srpPrice * quantity;
            totalBundlePrice += bundlePrice * quantity;
            // Accumulate wattage
            const watts = parseInt(selectedOption.getAttribute('data-watts')) || 0;
            totalWattage += watts * quantity;
            // Format prices with commas
            const regularPriceText = `₱${(regularPrice * quantity).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
            const srpText = `₱${(srpPrice * quantity).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
            const bundleText = `₱${(bundlePrice * quantity).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
            summaryHTML += `
                <div class="flex items-start pb-2">
                    <div class="mr-2 mt-1">
                        <i data-lucide="${getCategoryIcon(category)}" class="w-4 h-4 text-primary-400"></i>
                    </div>
                    <div class="flex-1">
                        <div class="text-sm font-medium text-gray-300">${category}</div>
                        <div class="text-xs text-gray-400">${itemName}</div>
                        <div class="flex justify-between items-center mt-1">
                            <div class="text-xs">
                                <span class="text-gray-400">(Regular Price)</span>
                                <span class="text-blue-400 ml-1">${regularPriceText}</span>
                            </div>
                            <div class="text-xs">
                                <span class="text-gray-400">(SRP)</span>
                                <span class="text-green-400 ml-1">${srpText}</span>
                            </div>
                            <div class="text-xs">
                                <span class="text-gray-400">(Bundle)</span>
                                <span class="text-primary-400 ml-1">${bundleText}</span>
                            </div>
                        </div>
                        ${quantity > 1 ? `<div class='text-xs text-primary-400'>Quantity: ${quantity}</div>` : ''}
                    </div>
                </div>
            `;
        }
    });

    if (selectedCount === 0) {
        summaryHTML = '<p class="text-gray-400 text-center">Select components to see your build summary</p>';
    }

    // Update the summary display
    summary.innerHTML = summaryHTML;
    
    // Update price displays
    document.getElementById('regularPrice').textContent = totalRegularPrice.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('srpPrice').textContent = totalSRP.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('bundleCashPrice').textContent = totalBundlePrice.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    // Update total wattage display
    document.getElementById('totalWattage').textContent = totalWattage;
    
    // Calculate and display discounts
    const srpDiscount = totalSRP - totalBundlePrice;
    const regularPriceDiscount = totalRegularPrice - totalBundlePrice;
    
    document.getElementById('srpDiscount').textContent = srpDiscount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('regularPriceDiscount').textContent = regularPriceDiscount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    
    // Refresh Lucide icons
    lucide.createIcons();
}
const paymentMethodLabels = {
  cash: "Discounted Cash",
  cc_straight: "CC Straight",
  bank_reg: "BDO / BPI Reg Installment",
  bdo_zero: "BDO 0%",
  homecredit: "Home Credit 0%"
};

function updatePaymentMethodRow() {
  const selected = localStorage.getItem('selectedPaymentMethod') || 'cash';
  const el = document.getElementById('selectedPaymentMethodText');
  if (el) {
    el.textContent = paymentMethodLabels[selected] || '';
  }
}

// Update on page load
updatePaymentMethodRow();

// Listen for changes to localStorage (even from other tabs/components)
window.addEventListener('storage', function(e) {
  if (e.key === 'selectedPaymentMethod') {
    updatePaymentMethodRow();
  }
});

// Also update every second as a fallback
setInterval(updatePaymentMethodRow, 1000);
setTimeout(updatePaymentMethodRow, 500);
</script>