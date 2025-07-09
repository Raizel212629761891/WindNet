/**
 * Price Calculator for Wind Net PC Builder
 * This script calculates the total prices based on SRP and Cash_Price
 */

// Store component quantities
const componentQuantities = {};

// Calculate the total price based on selected components
function calculateTotal() {
    console.log('Calculating total prices');
    let totalSRP = 0;
    let totalRegularPrice = 0;
    let totalBundlePrice = 0;

    const selects = document.querySelectorAll('#pcBuilderForm select');
    
    selects.forEach(sel => {
        const category = sel.getAttribute('data-category').toLowerCase();
        const selectedOption = sel.selectedOptions[0];
        
        // Skip placeholder options
        if (selectedOption.value === '0') return;
        
        // Get price values from data attributes
        const bundlePrice = parseFloat(selectedOption.getAttribute('data-bundle-cash-price')) || 0;
        const regularPrice = parseFloat(selectedOption.getAttribute('data-regular-price')) || 0;
        const srpPrice = parseFloat(selectedOption.getAttribute('data-srp')) || 0;
        
        // Get quantity for this component
        const quantityInput = document.getElementById(`quantity-${category}`);
        const quantity = quantityInput ? parseInt(quantityInput.value) || 1 : 1;
        
        // Add to totals with quantity
        totalSRP += srpPrice * quantity;
        totalRegularPrice += regularPrice * quantity;
        totalBundlePrice += bundlePrice * quantity;
    });
    
    // Update the price displays
    document.getElementById('srpPrice').textContent = totalSRP.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('regularPrice').textContent = totalRegularPrice.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('bundleCashPrice').textContent = totalBundlePrice.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    
    // Calculate and display discounts
    const srpDiscount = totalSRP - totalBundlePrice;
    const regularPriceDiscount = totalRegularPrice - totalBundlePrice;
    
    document.getElementById('srpDiscount').textContent = srpDiscount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('regularPriceDiscount').textContent = regularPriceDiscount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    
    // Update payment method prices
    const paymentMethod = document.querySelector('input[name="paymentMethod"]:checked')?.value || 'cash';
    let finalPrice = 0;
    
    if (paymentMethod === 'cash') {
        finalPrice = totalBundlePrice;
    } else if (paymentMethod === 'cc_straight') {
        finalPrice = totalRegularPrice;
    } else if (paymentMethod === 'bank_reg') {
        finalPrice = totalRegularPrice * 1.03;
    } else if (paymentMethod === 'bdo_zero') {
        finalPrice = totalRegularPrice;
    } else if (paymentMethod === 'homecredit') {
        finalPrice = totalSRP;
    }
    
    // Update payment method display
    document.getElementById('originalPrice').textContent = totalBundlePrice.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('installmentPriceDisplay').textContent = totalRegularPrice.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('cashDiscount').textContent = (totalRegularPrice - totalBundlePrice).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('finalTotal').textContent = finalPrice.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
}

// Update individual component price displays
function updateComponentSubtotals() {
    const selects = document.querySelectorAll('#pcBuilderForm select');
    
    selects.forEach(sel => {
        const category = sel.getAttribute('data-category').toLowerCase();
        const selectedOption = sel.selectedOptions[0];
        
        // Skip placeholder options
        if (selectedOption.value === '0') {
            // Reset price displays
            const srpElement = document.getElementById(`srp-price-${category}`);
            const regularElement = document.getElementById(`regular-price-${category}`);
            const bundleElement = document.getElementById(`bundle-price-${category}`);
            
            if (srpElement) srpElement.textContent = '₱0.00';
            if (regularElement) regularElement.textContent = '₱0.00';
            if (bundleElement) bundleElement.textContent = '₱0.00';
            return;
        }
        
        // Get price values from data attributes
        const bundlePrice = parseFloat(selectedOption.getAttribute('data-bundle-cash-price')) || 0;
        const regularPrice = parseFloat(selectedOption.getAttribute('data-regular-price')) || 0;
        const srpPrice = parseFloat(selectedOption.getAttribute('data-srp')) || 0;
        
        // Get quantity
        const quantityInput = document.getElementById(`quantity-${category}`);
        const quantity = quantityInput ? parseInt(quantityInput.value) || 1 : 1;
        
        // Calculate subtotals
        const bundleSubtotal = bundlePrice * quantity;
        const regularSubtotal = regularPrice * quantity;
        const srpSubtotal = srpPrice * quantity;
        
        // Update price displays
        const srpElement = document.getElementById(`srp-price-${category}`);
        const regularElement = document.getElementById(`regular-price-${category}`);
        const bundleElement = document.getElementById(`bundle-price-${category}`);
        
        if (srpElement) {
            srpElement.textContent = `₱${srpSubtotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
        }
        
        if (regularElement) {
            regularElement.textContent = `₱${regularSubtotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
        }
        
        if (bundleElement) {
            bundleElement.textContent = `₱${bundleSubtotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
        }
    });
}

// Update component prices when selection changes
function updateComponentPrices(selectElement) {
    const category = selectElement.getAttribute('data-category').toLowerCase();
    const selectedOption = selectElement.selectedOptions[0];
    
    // Get price display elements
    const regularPriceElement = document.getElementById(`regular-price-${category}`);
    const cashPriceElement = document.getElementById(`cash-price-${category}`);
    
    if (!regularPriceElement || !cashPriceElement) {
        console.error(`Price display elements not found for ${category}`);
        return;
    }
    
    // Reset prices if placeholder is selected
    if (selectedOption.value === '0') {
        regularPriceElement.textContent = '₱0.00';
        cashPriceElement.textContent = '₱0.00';
        return;
    }
    
    // Get SRP and Cash_Price values
    const cashPrice = parseFloat(selectedOption.getAttribute('data-price')) || 0;
    const srpPrice = parseFloat(selectedOption.getAttribute('data-installment')) || 0;
    
    // Get quantity
    const quantityInput = document.getElementById(`quantity-${category}`);
    const quantity = quantityInput ? parseInt(quantityInput.value) || 1 : 1;
    
    // Calculate subtotals
    const cashSubtotal = cashPrice * quantity;
    const srpSubtotal = srpPrice * quantity;
    
    // Update price displays
    regularPriceElement.textContent = `₱${srpSubtotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
    cashPriceElement.textContent = `₱${cashSubtotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
}

// Update the build summary with selected components
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
        
        // Get SRP and Cash_Price values
        const cashPrice = parseFloat(selectedOption.getAttribute('data-price')) || 0;
        const srpPrice = parseFloat(selectedOption.getAttribute('data-installment')) || 0;
        
        // Get quantity
        const quantityInput = document.getElementById(`quantity-${category.toLowerCase()}`);
        const quantity = quantityInput ? parseInt(quantityInput.value) || 1 : 1;
        
        // Calculate subtotals
        const cashSubtotal = cashPrice * quantity;
        const srpSubtotal = srpPrice * quantity;
        
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
                    <p class="font-medium">₱${srpSubtotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</p>
                    <p class="text-sm text-primary-400">₱${cashSubtotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</p>
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

// Initialize event listeners
document.addEventListener('DOMContentLoaded', function() {
    // Initial calculation
    calculateTotal();
    
    // Set up MutationObserver to watch for changes in the form
    const form = document.getElementById('pcBuilderForm');
    if (form) {
        const observer = new MutationObserver(function(mutations) {
            calculateTotal();
            updateBuildSummary();
        });
        
        observer.observe(form, { 
            childList: true, 
            subtree: true, 
            attributes: true, 
            attributeFilter: ['value', 'selected'] 
        });
    }
    
    // Set up event listeners for quantity changes
    document.querySelectorAll('.quantity-input').forEach(input => {
        input.addEventListener('change', function() {
            calculateTotal();
            updateBuildSummary();
        });
    });
    
    // Update build summary initially
    updateBuildSummary();
});
