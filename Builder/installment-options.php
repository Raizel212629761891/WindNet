<?php
// Installment Options Component for PC Builder
?>

<div class="bg-gray-800 bg-opacity-50 backdrop-blur-md rounded-2xl shadow-2xl border border-white border-opacity-10 overflow-hidden">
    <div class="p-4 bg-gray-900 border-b border-gray-700">
        <h3 class="text-xl font-semibold flex items-center">
            <i data-lucide="credit-card" class="w-5 h-5 mr-2 text-primary-400"></i>
            Payment Options
        </h3>
    </div>
    
    <div class="p-5">
        <!-- Payment Method Selection -->
        <div class="mb-5">
            <h4 class="text-sm font-semibold text-gray-300 mb-3">Payment Method</h4>
            
            <div class="space-y-2">
                <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="radio" name="paymentMethod" value="cash" class="form-radio text-primary-500 focus:ring-primary-500" checked>
                    <span class="text-sm">Discounted Cash</span>
                </label>
                
                <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="radio" name="paymentMethod" value="cc_straight" class="form-radio text-primary-500 focus:ring-primary-500">
                    <span class="text-sm">CC Straight</span>
                </label>
                
                <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="radio" name="paymentMethod" value="bank_reg" class="form-radio text-primary-500 focus:ring-primary-500">
                    <span class="text-sm">BDO / BPI Reg Installment</span>
                </label>
                
                <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="radio" name="paymentMethod" value="bdo_zero" class="form-radio text-primary-500 focus:ring-primary-500">
                    <span class="text-sm">BDO 0%</span>
                </label>
                
                <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="radio" name="paymentMethod" value="homecredit" class="form-radio text-primary-500 focus:ring-primary-500">
                    <span class="text-sm">Home Credit 0%</span>
                </label>
            </div>
        </div>
        
        <!-- Payment Details Container -->
        <div id="installmentMonthsContainer" class="mb-5">
            <!-- Installment dropdown hidden but kept for JavaScript functionality -->
            <div style="display: none;">
                <select id="installmentMonths" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg p-2.5 focus:ring-primary-500 focus:border-primary-500">
                    <option value="12">12 months</option>
                    <option value="15">15 months</option>
                    <option value="18">18 months</option>
                </select>
            </div>
            
            <!-- Home Credit Payment Details -->
            <div id="homeCreditPaymentDetails" class="mt-3 bg-gray-800 rounded-lg p-3 border border-gray-700">
                <div class="text-sm text-gray-300 font-semibold mb-2">Home Credit Payment Details:</div>
                <div class="text-sm space-y-1">
                    <div class="flex justify-between">
                        <span>Total Price (SRP):</span>
                        <span class="text-white" id="homeCreditTotal">₱0.00</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span>Down Payment:</span>
                        <div class="flex items-center">
                            <span class="text-yellow-300 mr-2">₱</span>
                            <input type="number" id="homeCreditDownPaymentInput" class="w-24 bg-gray-700 border border-gray-600 text-white rounded-lg p-1 text-right" value="0" min="0" step="1000">
                        </div>
                    </div>
                    <div class="flex items-center justify-between mt-1">
                        <span class="text-xs text-gray-400">Down Payment %:</span>
                        <span class="text-xs text-gray-400" id="downPaymentPercent">0%</span>
                    </div>
                    <div class="border-t border-gray-700 my-2 pt-2">
                        <div class="font-semibold mb-1">Monthly Payments:</div>
                    </div>
                    <div class="flex justify-between">
                        <span>12 months:</span>
                        <span class="text-primary-300" id="monthly12">₱0.00</span>
                    </div>
                    <div class="flex justify-between">
                        <span>15 months:</span>
                        <span class="text-primary-300" id="monthly15">₱0.00</span>
                    </div>
                    <div class="flex justify-between">
                        <span>18 months:</span>
                        <span class="text-primary-300" id="monthly18">₱0.00</span>
                    </div>
                </div>
            </div>
            
            <!-- BDO 0% Payment Details -->
            <div id="bdoZeroPaymentDetails" class="mt-3 bg-gray-800 rounded-lg p-3 border border-gray-700" style="display: none;">
                <div class="text-sm text-gray-300 font-semibold mb-2">BDO 0% Payment Details:</div>
                <div class="text-sm space-y-1">
                    <div class="flex justify-between">
                        <span>Total Price:</span>
                        <span class="text-white" id="bdoZeroTotal">₱0.00</span>
                    </div>
                    <div class="border-t border-gray-700 my-2 pt-2">
                        <div class="font-semibold mb-1">Payment Breakdown:</div>
                    </div>
                    <div class="flex justify-between">
                        <span>3 months (monthly):</span>
                        <span class="text-primary-300" id="bdoMonthly">₱0.00</span>
                    </div>
                </div>
            </div>
            
            <!-- BDO/BPI Reg Installment Payment Details -->
            <div id="bankRegPaymentDetails" class="mt-3 bg-gray-800 rounded-lg p-3 border border-gray-700" style="display: none;">
                <div class="text-sm text-gray-300 font-semibold mb-2">BDO/BPI Reg Installment Details:</div>
                <div class="text-sm space-y-1">
                    <div class="flex justify-between">
                        <span>Total Price:</span>
                        <span class="text-white" id="bankRegTotal">₱0.00</span>
                    </div>
                    <div class="border-t border-gray-700 my-2 pt-2">
                        <div class="font-semibold mb-1">Payment Options:</div>
                    </div>
                    <div class="flex justify-between">
                        <span>3 months (monthly):</span>
                        <span class="text-primary-300" id="bankReg3">₱0.00</span>
                    </div>
                    <div class="flex justify-between">
                        <span>6 months (monthly):</span>
                        <span class="text-primary-300" id="bankReg6">₱0.00</span>
                    </div>
                    <div class="flex justify-between">
                        <span>9 months (monthly):</span>
                        <span class="text-primary-300" id="bankReg9">₱0.00</span>
                    </div>
                    <div class="flex justify-between">
                        <span>12 months (monthly):</span>
                        <span class="text-primary-300" id="bankReg12">₱0.00</span>
                    </div>
                    <div class="flex justify-between">
                        <span>18 months (monthly):</span>
                        <span class="text-primary-300" id="bankReg18">₱0.00</span>
                    </div>
                    <div class="flex justify-between">
                        <span>24 months (monthly):</span>
                        <span class="text-primary-300" id="bankReg24">₱0.00</span>
                    </div>
                    <div class="flex justify-between">
                        <span>36 months (monthly):</span>
                        <span class="text-primary-300" id="bankReg36">₱0.00</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Price Calculation -->
        <div id="priceCalculationBox" class="bg-gray-800 rounded-lg p-4 border border-gray-700">
            <div class="space-y-2">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-400">Total Price:</span>
                    <span class="text-sm font-medium">₱<span id="originalPrice">0.00</span></span>
                </div>
                
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-400">Installment Price:</span>
                    <span class="text-sm font-medium text-red-400">₱<span id="installmentPriceDisplay">0.00</span></span>
                </div>
                
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-400">Cash Payment Discount:</span>
                    <span class="text-sm font-medium text-green-400">-₱<span id="cashDiscount">0.00</span></span>
                </div>
                
                <div class="border-t border-gray-700 pt-2 mt-2">
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-semibold text-gray-300">Final Total:</span>
                        <span class="text-lg font-bold text-primary-400">₱<span id="finalTotal">0.00</span></span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Save Quotation -->
        <div class="mt-5">
            <button id="saveQuotationBtn" class="w-full px-4 py-3 bg-primary-600 hover:bg-primary-500 text-white font-semibold rounded-lg transition duration-300 flex items-center justify-center">
                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i>
                Save Quotation
            </button>
        </div>

        
        <!-- Installment Information -->
        <div class="mt-5 bg-gray-800 rounded-lg p-3 border border-gray-700">
            <div class="flex items-center mb-2">
                <i data-lucide="info" class="w-4 h-4 text-primary-400 mr-2"></i>
                <h4 class="text-sm font-semibold text-gray-300">Installment Information</h4>
            </div>
            
            <div id="installmentInfo" class="text-xs text-gray-400 space-y-1">
                <p>• BDO/PNB: 0% interest for 6 months</p>
                <p>• Home Credit: 0% interest for 3 months</p>
                <p>• Cash payment receives 5% discount</p>
                <p>• All prices include VAT</p>
            </div>
        </div>
    </div>
</div>

<script>
// Initialize installment options functionality
document.addEventListener('DOMContentLoaded', function() {
    const paymentMethods = document.querySelectorAll('input[name="paymentMethod"]');
    const paymentMethodLabels = {
        cash: "Discounted Cash",
        cc_straight: "CC Straight",
        bank_reg: "BDO / BPI Reg Installment",
        bdo_zero: "BDO 0%",
        homecredit: "Home Credit 0%"
    };
    function showHidePaymentSections(selectedMethod) {
        const isHomeCredit = selectedMethod === 'homecredit';
        const isBdoZero = selectedMethod === 'bdo_zero';
        const isBankReg = selectedMethod === 'bank_reg';
        const isCash = selectedMethod === 'cash';
        const isCCStraight = selectedMethod === 'cc_straight';

        // Show/hide price calculation box
        const priceCalculationBox = document.getElementById('priceCalculationBox');
        if (priceCalculationBox) {
            priceCalculationBox.style.display = (isCash || isCCStraight) ? 'block' : 'none';
        }
        // Home Credit elements
        const homeCreditElements = document.querySelectorAll('.home-credit-only');
        homeCreditElements.forEach(el => {
            el.style.display = isHomeCredit ? 'block' : 'none';
        });
        // Show/hide payment details containers
        document.getElementById('homeCreditPaymentDetails').style.display = isHomeCredit ? 'block' : 'none';
        document.getElementById('bdoZeroPaymentDetails').style.display = isBdoZero ? 'block' : 'none';
        document.getElementById('bankRegPaymentDetails').style.display = isBankReg ? 'block' : 'none';
        
        // The heading no longer exists, so we don't need to access it
        // Just keep the installment months dropdown hidden at all times
        const installmentMonths = document.getElementById('installmentMonths');
        if (installmentMonths) {
            installmentMonths.parentElement.style.display = 'none';
        }
    }
    paymentMethods.forEach(method => {
        method.addEventListener('change', function() {
            localStorage.setItem('selectedPaymentMethod', this.value);
            // Directly update summary if present
            const el = document.getElementById('selectedPaymentMethodText');
            if (el) {
                el.textContent = paymentMethodLabels[this.value] || '';
            }
            showHidePaymentSections(this.value);
            updateFinalPrice();
        });
    });
    // On page load, set the initial value, update summary, and recalculate formulas
    const checked = document.querySelector('input[name="paymentMethod"]:checked');
    if (checked) {
        localStorage.setItem('selectedPaymentMethod', checked.value);
        const el = document.getElementById('selectedPaymentMethodText');
        if (el) {
            el.textContent = paymentMethodLabels[checked.value] || '';
        }
        showHidePaymentSections(checked.value);
        updateFinalPrice();
    }
    
    // Installment months change handler
    const installmentMonths = document.getElementById('installmentMonths');
    if (installmentMonths) {
        installmentMonths.addEventListener('change', updateFinalPrice);
    }
    
    // Down payment input handler
    const downPaymentInput = document.getElementById('homeCreditDownPaymentInput');
    if (downPaymentInput) {
        // Use 'input' event for real-time updates as the user types
        downPaymentInput.addEventListener('input', function() {
            // Immediately update calculations when the user changes the input
            updateFinalPrice();
        });
        
        // Also keep the 'change' event for when the input loses focus
        downPaymentInput.addEventListener('change', function() {
            updateFinalPrice();
        });
        
        // Initialize with default 40% value on first load
        if (downPaymentInput.value === '0' || downPaymentInput.value === '') {
            const totalPrice = parseFloat(document.getElementById('totalPrice')?.textContent.replace(/[^\d.-]/g, '')) || 0;
            if (totalPrice > 0) {
                downPaymentInput.value = Math.round(totalPrice * 0.4);
                downPaymentInput.setAttribute('data-initialized', 'true');
                updateFinalPrice();
            }
        }
    }
    
    // Add to cart button
    const addToCartBtn = document.getElementById('addToCartBtn');
    if (addToCartBtn) {
        addToCartBtn.addEventListener('click', function() {
            alert('Build added to cart!');
        });
    }
    
    // Set up a MutationObserver to watch for changes in the build summary prices
    const totalPriceElement = document.getElementById('totalPrice');
    const installmentPriceElement = document.getElementById('installmentPrice');
    
    if (totalPriceElement && installmentPriceElement) {
        // Create an observer instance
        const observer = new MutationObserver(function(mutations) {
            // When build summary prices change, update the payment options prices
            updateFinalPrice();
        });
        
        // Observe both price elements for text content changes
        observer.observe(totalPriceElement, { childList: true, characterData: true, subtree: true });
        observer.observe(installmentPriceElement, { childList: true, characterData: true, subtree: true });
    }
    
    // Also set up an interval to periodically check for price changes
    // This is a fallback in case the MutationObserver doesn't catch all changes
    setInterval(updateFinalPrice, 1000);
    
    // Initial price update
    updateFinalPrice();
});

// Function to update the final price based on selections
function updateFinalPrice() {
    // Get the selected components and their prices
    const selectedComponents = getSelectedComponents();
    
    // Calculate prices based on different database columns
    let cashPrice = 0;         // Bundle_Cash_Price
    let regularPrice = 0;      // Regular_Price
    let srpPrice = 0;          // SRP
    
    // Check if motherboard is selected
    const motherboardSelect = document.querySelector('select[data-category="Motherboard"]');
    const hasMotherboard = motherboardSelect && motherboardSelect.value !== '0';
    
    // Loop through all selected components to calculate different price types
    document.querySelectorAll('#pcBuilderForm select').forEach(select => {
        if (select.value && select.value !== '0') {
            const selectedOption = select.options[select.selectedIndex];
            const category = select.getAttribute('data-category').toLowerCase();
            // Get prices from data attributes
            let bundleCashPrice = 0;
            if (category === 'cpu' && hasMotherboard) {
                bundleCashPrice = parseFloat(selectedOption.getAttribute('data-bundle-cash-price')) || 0;
            } else {
                bundleCashPrice = parseFloat(selectedOption.getAttribute('data-price')) || 0;
            }
            const regPrice = parseFloat(selectedOption.getAttribute('data-regular-price')) || 0;
            const srpValue = parseFloat(selectedOption.getAttribute('data-srp')) || 0;
            // Get quantity
            const quantityInput = document.getElementById(`quantity-${category}`);
            const quantity = quantityInput ? parseInt(quantityInput.value) || 1 : 1;
            // Add to respective totals (multiply by quantity)
            cashPrice += bundleCashPrice * quantity;
            regularPrice += regPrice * quantity;
            srpPrice += srpValue * quantity;
        }
    });
    
    // Get selected payment method
    const selectedPaymentMethod = document.querySelector('input[name="paymentMethod"]:checked')?.value || 'cash';
    
    // Calculate final price based on payment method
    let finalPrice = 0;
    
    if (selectedPaymentMethod === 'cash') {
        // Discounted Cash - uses Bundle_Cash_Price
        finalPrice = cashPrice;
    } else if (selectedPaymentMethod === 'cc_straight') {
        // CC Straight - uses Regular_Price
        finalPrice = regularPrice;
    } else if (selectedPaymentMethod === 'bank_reg') {
        // BDO/BPI Reg Installment - uses Regular_Price with 3% interest
        finalPrice = regularPrice * 1.03;
    } else if (selectedPaymentMethod === 'bdo_zero') {
        // BDO 0% - uses Regular_Price
        finalPrice = regularPrice;
    } else if (selectedPaymentMethod === 'homecredit') {
        // Home Credit - uses SRP
        finalPrice = srpPrice;
        
        // Home Credit specific calculations
        const months = document.getElementById('installmentMonths').value;
        
        // Set default down payment to 40% of SRP if the field is empty or 0
        const downPaymentInput = document.getElementById('homeCreditDownPaymentInput');
        let downPayment = parseFloat(downPaymentInput.value) || 0;
        
        // If the down payment is 0 or the field was just focused for the first time, set to 40%
        if (downPayment === 0 || downPaymentInput.getAttribute('data-initialized') !== 'true') {
            downPayment = srpPrice * 0.4; // 40% of SRP
            downPaymentInput.value = downPayment.toFixed(0);
            downPaymentInput.setAttribute('data-initialized', 'true');
        }
        
        // Calculate monthly payment based on the remaining amount after down payment
        const remainingAmount = srpPrice - downPayment;
        const monthly12 = remainingAmount / 12;
        const monthly15 = remainingAmount / 15;
        const monthly18 = remainingAmount / 18;
        
        // Update Home Credit payment details
        document.getElementById('homeCreditTotal').textContent = '₱' + srpPrice.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('downPaymentPercent').textContent = ((downPayment / srpPrice) * 100).toFixed(1) + '%';
        
        // Update monthly payments based on the remaining amount
        document.getElementById('monthly12').textContent = '₱' + monthly12.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('monthly15').textContent = '₱' + monthly15.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('monthly18').textContent = '₱' + monthly18.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        
        // Update installment info
        const downPaymentPercent = ((downPayment / srpPrice) * 100).toFixed(1);
        const installmentInfo = document.getElementById('installmentInfo');
        if (installmentInfo) {
            installmentInfo.innerHTML = `
                <p>• Home Credit 0% interest installment</p>
                <p>• Down payment (${downPaymentPercent}%): ₱${downPayment.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</p>
                <p>• Monthly for ${months} months: ₱${(remainingAmount / parseInt(months)).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</p>
                <p>• Total payment: ₱${srpPrice.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</p>
                <p>• Valid ID and proof of billing required</p>
                <p>• With insurance coverage</p>
            `;
        }
    }
    
    // Update the display with formatted numbers (commas)
    document.getElementById('originalPrice').textContent = cashPrice.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('installmentPriceDisplay').textContent = regularPrice.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('cashDiscount').textContent = (regularPrice - cashPrice).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('finalTotal').textContent = finalPrice.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    
    // Update installment information based on selected payment method
    const installmentInfo = document.getElementById('installmentInfo');
    if (installmentInfo) {
        if (selectedPaymentMethod === 'cash') {
            installmentInfo.innerHTML = `
                <p>• Cash payment receives 5% discount</p>
                <p>• GCash, PayMaya, and bank transfers accepted</p>
                <p>• Same-day processing for cash payments</p>
                <p>• All prices include VAT</p>
            `;
        } else if (selectedPaymentMethod === 'cc_straight') {
            installmentInfo.innerHTML = `
                <p>• One-time payment using credit card</p>
                <p>• All major credit cards accepted</p>
                <p>• No additional fees</p>
                <p>• All prices include VAT</p>
            `;
        } else if (selectedPaymentMethod === 'bank_reg') {
            // Use exact factors to match the expected values
            // For a total of 10,094, we need 3-month payment to be 3,364.66
            const reg_3months = parseFloat((finalPrice * (3364.66 / 10094)).toFixed(2));
            const reg_6months = parseFloat((finalPrice * (1731.34 / 10094)).toFixed(2));
            const reg_9months = parseFloat((finalPrice * (1186.89 / 10094)).toFixed(2));
            const reg_12months = parseFloat((finalPrice * (914.66 / 10094)).toFixed(2));
            const reg_18months = parseFloat((finalPrice * (642.45 / 10094)).toFixed(2));
            const reg_24months = parseFloat((finalPrice * (506.34 / 10094)).toFixed(2));
            const reg_36months = parseFloat((finalPrice * (369.24 / 10094)).toFixed(2));
            
            // Update BDO/BPI Reg Installment payment details
            document.getElementById('bankRegTotal').textContent = '₱' + finalPrice.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            document.getElementById('bankReg3').textContent = '₱' + reg_3months.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            document.getElementById('bankReg6').textContent = '₱' + reg_6months.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            document.getElementById('bankReg9').textContent = '₱' + reg_9months.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            document.getElementById('bankReg12').textContent = '₱' + reg_12months.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            document.getElementById('bankReg18').textContent = '₱' + reg_18months.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            document.getElementById('bankReg24').textContent = '₱' + reg_24months.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            document.getElementById('bankReg36').textContent = '₱' + reg_36months.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            
            installmentInfo.innerHTML = `
                <p>• Multiple installment terms available (3-36 months)</p>
                <p>• 3% interest with BDO/BPI regular installment</p>
                <p>• Minimum purchase of ₱3,000</p>
                <p>• All prices include VAT</p>
            `;
        } else if (selectedPaymentMethod === 'bdo_zero') {
            const monthlyPayment = parseFloat((finalPrice / 3).toFixed(2));
            
            // Update BDO 0% payment details
            document.getElementById('bdoZeroTotal').textContent = '₱' + finalPrice.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            document.getElementById('bdoMonthly').textContent = '₱' + monthlyPayment.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            
            installmentInfo.innerHTML = `
                <p>• Monthly payment: ₱${monthlyPayment.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})} for 3 months</p>
                <p>• 0% interest for 3 months with BDO credit card</p>
                <p>• Minimum purchase of ₱5,000</p>
                <p>• All prices include VAT</p>
            `;
        }
    }
}

// Helper function to format numbers with commas
function formatNumberWithCommas(number) {
    return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

function updateInstallmentOptions(select) {
    const priceDisplay = document.querySelector('.price-display');
    const paymentMethod = select.value;
    
    // Hide price display for BDO and BPI regular installment
    if (paymentMethod === 'bdo_regular' || paymentMethod === 'bpi_regular') {
        priceDisplay.style.display = 'none';
    } else {
        priceDisplay.style.display = 'block';
    }
    
    // Update prices based on selected payment method
    calculateTotal();
}

// Save Quotation Button Functionality
document.getElementById('saveQuotationBtn').addEventListener('click', function() {
    // Get selected components
    const selects = document.querySelectorAll('#pcBuilderForm select');
    let selectedComponents = [];
    
    // Get prices from the correct elements
    const bundleCashPriceElement = document.getElementById('bundleCashPrice');
    const regularPriceElement = document.getElementById('regularPrice');
    const srpPriceElement = document.getElementById('srpPrice');
    
    if (!bundleCashPriceElement || !regularPriceElement || !srpPriceElement) {
        alert('Error: Could not find price elements. Please try again.');
        return;
    }
    
    // Parse prices, removing currency symbol and commas
    const totalPrice = parseFloat(bundleCashPriceElement.textContent.replace(/[^\d.]/g, '')) || 0;
    const regularPrice = parseFloat(regularPriceElement.textContent.replace(/[^\d.]/g, '')) || 0;
    const srpPrice = parseFloat(srpPriceElement.textContent.replace(/[^\d.]/g, '')) || 0;
    
    // Calculate price difference
    const priceDifference = regularPrice - totalPrice;
    
    // Create customer info modal
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 flex items-center justify-center z-50';
    modal.innerHTML = `
        <div class="fixed inset-0 bg-black opacity-70"></div>
        <div class="bg-gray-900 p-6 rounded-lg shadow-xl border border-gray-700 w-full max-w-md z-10">
            <h3 class="text-xl font-semibold mb-4 text-white">Customer Information</h3>
            <form id="quotationForm" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-1">Customer Name</label>
                    <input type="text" id="customerName" class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-primary-500" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-1">Contact Number</label>
                    <input type="text" id="customerContact" class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-primary-500" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-1">Notes</label>
                    <textarea id="notes" class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-primary-500 h-24"></textarea>
                </div>
                <div class="flex justify-end space-x-3 pt-2">
                    <button type="button" id="cancelQuotation" class="px-4 py-2 bg-gray-700 text-white rounded-md hover:bg-gray-600 transition">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-500 transition">Save Quotation</button>
                </div>
            </form>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    // Cancel button event
    document.getElementById('cancelQuotation').addEventListener('click', function() {
        document.body.removeChild(modal);
    });
    
    // Form submission
    document.getElementById('quotationForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Get form values
        const customerName = document.getElementById('customerName').value;
        const customerContact = document.getElementById('customerContact').value;
        const notes = document.getElementById('notes').value;
        
        // Get selected components data
        selects.forEach(sel => {
            const category = sel.getAttribute('data-category');
            const value = sel.value;
            const selectedOption = sel.selectedOptions[0];
            
            if (value !== '0') {
                // Extract just numeric part if value contains non-numeric characters
                const rawInventoryId = value;
                // Try to extract inventory ID from dropdown option value or data attribute
                let inventoryId;
                
                // Check if the value is a valid inventory ID
                if (selectedOption.hasAttribute('data-inventory-id')) {
                    // Use dedicated inventory ID attribute if available
                    inventoryId = selectedOption.getAttribute('data-inventory-id');
                } else if (!isNaN(parseInt(rawInventoryId))) {
                    // If the value is numerical, use it directly
                    inventoryId = parseInt(rawInventoryId);
                } else {
                    // Otherwise, try to extract a numeric ID from text or value
                    const match = rawInventoryId.match(/(\d+)/);
                    inventoryId = match ? parseInt(match[0]) : null;
                }
                
                // Skip if we couldn't determine a valid inventory ID
                if (!inventoryId) {
                    console.warn('Could not determine inventory ID for:', category, rawInventoryId);
                    return;
                }
                
                const price = parseFloat(selectedOption.getAttribute('data-price')) || 0;
                const quantity = 1; // Default quantity
                
                console.log('Adding component:', {
                    category: category,
                    value: rawInventoryId,
                    inventory_id: inventoryId, 
                    price: price
                });
                
                selectedComponents.push({
                    inventory_id: inventoryId,
                    quantity: quantity,
                    price: price
                });
            }
        });
        
        // Only proceed if there are components selected
        if (selectedComponents.length === 0) {
            alert('Please select at least one component before saving a quotation.');
            document.body.removeChild(modal);
            return;
        }
        
        // Log data being sent
        const requestData = {
            customer_name: customerName,
            customer_contact: customerContact,
            notes: notes,
            final_price: totalPrice,
            total_discount: priceDifference,
            items: selectedComponents
        };
        
        console.log('Sending data to server:', requestData);
        
        // Send data to server
        fetch('../Builder/save_quotation.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(requestData)
        })
        .then(response => {
            console.log('Server response:', response);
            return response.json();
        })
        .then(data => {
            console.log('Parsed data:', data);
            if (data.success) {
                // Show success message
                const successModal = document.createElement('div');
                successModal.className = 'fixed inset-0 flex items-center justify-center z-50';
                successModal.innerHTML = `
                    <div class="fixed inset-0 bg-black opacity-70"></div>
                    <div class="bg-gray-900 p-6 rounded-lg shadow-xl border border-gray-700 w-full max-w-md z-10">
                        <div class="text-center">
                            <div class="mb-4 flex justify-center">
                                <i data-lucide="check-circle" class="w-16 h-16 text-green-500"></i>
                            </div>
                            <h3 class="text-xl font-semibold mb-2 text-white">Quotation Saved!</h3>
                            <p class="text-gray-400 mb-4">Quotation #${data.quotation_id} has been saved successfully.</p>
                            <button id="closeSuccessModal" class="px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-500 transition">OK</button>
                        </div>
                    </div>
                `;
                
                document.body.appendChild(successModal);
                lucide.createIcons();
                
                // Add event listener to the OK button to capture build summary
                document.getElementById('closeSuccessModal').addEventListener('click', function() {
                    // First, capture the build summary as an image
                    captureBuildSummary(data.quotation_id);
                    // Then close the modal
                    document.body.removeChild(successModal);
                });
            } else {
                alert('Error saving quotation: ' + data.message);
            }
            
            document.body.removeChild(modal);
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while saving the quotation');
            document.body.removeChild(modal);
        });
    });
    
    lucide.createIcons();
});

// Function to capture build summary as an image
function captureBuildSummary(quotationId) {
    // Load html2canvas library if not already loaded
    if (!window.html2canvas) {
        const script = document.createElement('script');
        script.src = 'https://html2canvas.hertzen.com/dist/html2canvas.min.js';
        script.onload = function() {
            performCapture();
        };
        document.head.appendChild(script);
    } else {
        performCapture();
    }
    
    function performCapture() {
        // Get the direct reference to the build summary section
        const buildSummaryEl = document.getElementById('buildSummary');
        const priceContainer = buildSummaryEl.parentElement.querySelector('.p-5.bg-gray-800');
        
        if (!buildSummaryEl || !priceContainer) {
            console.error('Could not find build summary elements');
            return;
        }
        
        // Create a new container for our screenshot
        const container = document.createElement('div');
        container.style.width = '500px';
        container.style.position = 'fixed';
        container.style.top = '-9999px';
        container.style.left = '-9999px';
        container.style.backgroundColor = '#1f2937';
        container.style.padding = '20px';
        container.style.borderRadius = '10px';
        container.style.boxShadow = '0 4px 6px rgba(0, 0, 0, 0.1)';
        container.style.zIndex = '-1';
        
        // Add static header
        const staticHeader = document.createElement('div');
        staticHeader.style.textAlign = 'center';
        staticHeader.style.marginBottom = '16px';
        staticHeader.innerHTML = `
            <div style="font-size: 1.3rem; font-weight: bold; color: #38bdf8; margin-bottom: 4px;">
                Wind Net PC Builder - Quotation #${quotationId}
            </div>
            <div style="margin-top: 4px; color: #fff; font-size: 1rem; line-height: 1.4;">
                Granja Corner Barcelona St, Brgy 1, Lucena City<br>
                Facebook.com/windnetpc | windnetpc.com<br>
                0912-039-0713(smart/viber) | (042) 719-2279 (PLDT)<br>
                For faster response send us a message/call via messenger
            </div>
        `;
        container.appendChild(staticHeader);
        
        // Clone the build summary content
        const summaryContent = buildSummaryEl.cloneNode(true);
        
        // Fix any image paths in the summary
        const images = summaryContent.querySelectorAll('img');
        images.forEach(img => {
            // Replace relative paths with absolute
            if (img.src && img.src.includes('/Wind%20Net/')) {
                img.src = 'https://windnet.com/placeholder-fixed.png';
            }
            
            // Add error handler to use a fallback image
            img.onerror = function() {
                this.src = 'https://windnet.com/placeholder-fixed.png';
                this.onerror = null;
            };
        });
        
        // Clone the price summary
        const priceSection = priceContainer.cloneNode(true);
        
        // Append everything to our container
        container.appendChild(summaryContent);
        container.appendChild(priceSection);
        
        // Add date footer
        const footer = document.createElement('div');
        const today = new Date();
        const date = today.toLocaleDateString('en-US', {
            year: 'numeric', 
            month: 'long', 
            day: 'numeric'
        });
        footer.innerHTML = `
            <div style="padding: 10px; text-align: right; color: #9ca3af; font-size: 12px; border-top: 1px solid #374151; margin-top: 15px;">
                Generated on ${date}
            </div>
        `;
        container.appendChild(footer);
        
        // Add to document temporarily
        document.body.appendChild(container);
        
        // Small delay to ensure the container is rendered
        setTimeout(() => {
            html2canvas(container, {
                backgroundColor: '#1f2937',
                scale: 2,
                logging: false,
                allowTaint: true,
                useCORS: true
            }).then(canvas => {
                try {
                    // Create download link
                    const link = document.createElement('a');
                    link.download = `Wind-Net-Quotation-${quotationId}.png`;
                    link.href = canvas.toDataURL('image/png');
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    
                    // Remove the container
                    document.body.removeChild(container);
                    
                    // Now also save to server
                    saveBuildImageToServer(canvas.toDataURL('image/png'), quotationId);
                } catch (error) {
                    console.error('Error creating screenshot:', error);
                    alert('There was an error creating the screenshot. The quotation was saved successfully.');
                    document.body.removeChild(container);
                }
            }).catch(error => {
                console.error('HTML2Canvas error:', error);
                alert('There was an error creating the screenshot. The quotation was saved successfully.');
                document.body.removeChild(container);
            });
        }, 300);
    }
}

// Function to save the captured image to server
function saveBuildImageToServer(imageData, quotationId) {
    fetch('../Builder/save_build_image.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            image_data: imageData,
            quotation_id: quotationId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Build image saved successfully');
        } else {
            console.error('Error saving build image:', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}
</script>
