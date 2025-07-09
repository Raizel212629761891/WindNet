/**
 * Price Synchronization Script
 * This script directly copies prices from the build summary to the payment options section
 */

document.addEventListener('DOMContentLoaded', function() {
    // Debounce function to prevent excessive updates
    function debounce(func, wait) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

    // Function to copy prices from build summary to payment options
    function syncPrices() {
        try {
            // Get the build summary prices
            const buildSummaryTotal = document.querySelector('.bg-gray-900 .p-5 .text-2xl #totalPrice');
            const buildSummaryInstallment = document.querySelector('.bg-gray-900 .p-5 .text-xl #installmentPrice');
            const buildSummaryDifference = document.querySelector('.bg-gray-900 .p-5 .text-sm #priceDifference');
            
            // Get the payment options price elements
            const paymentOptionsTotal = document.getElementById('originalPrice');
            const paymentOptionsInstallment = document.getElementById('installmentPriceDisplay');
            const paymentOptionsDiscount = document.getElementById('cashDiscount');
            
            // Copy the values directly only if they've changed
            if (buildSummaryTotal && paymentOptionsTotal && 
                buildSummaryTotal.textContent !== paymentOptionsTotal.textContent) {
                paymentOptionsTotal.textContent = buildSummaryTotal.textContent;
            }
            
            if (buildSummaryInstallment && paymentOptionsInstallment && 
                buildSummaryInstallment.textContent !== paymentOptionsInstallment.textContent) {
                paymentOptionsInstallment.textContent = buildSummaryInstallment.textContent;
            }
            
            if (buildSummaryDifference && paymentOptionsDiscount && 
                buildSummaryDifference.textContent !== paymentOptionsDiscount.textContent) {
                paymentOptionsDiscount.textContent = buildSummaryDifference.textContent;
            }
            
            // Update the final total based on selected payment method
            updateFinalTotal();
        } catch (error) {
            console.error('Error syncing prices:', error);
        }
    }
    
    // Function to update the final total based on payment method
    function updateFinalTotal() {
        try {
            // Get the selected payment method
            const selectedMethod = document.querySelector('input[name="paymentMethod"]:checked');
            if (!selectedMethod) return;
            
            // Get the payment option elements
            const totalPrice = document.getElementById('originalPrice');
            const installmentPrice = document.getElementById('installmentPriceDisplay');
            const finalTotal = document.getElementById('finalTotal');
            
            if (!totalPrice || !installmentPrice || !finalTotal) return;
            
            // Get the numeric values
            const totalValue = parseFloat(totalPrice.textContent.replace(/[^\d.]/g, '') || '0');
            const installmentValue = parseFloat(installmentPrice.textContent.replace(/[^\d.]/g, '') || '0');
            
            // Calculate final price based on payment method
            let finalValue = 0;
            
            switch (selectedMethod.value) {
                case 'cash':
                    finalValue = totalValue;
                    break;
                case 'cc_straight':
                    finalValue = installmentValue;
                    break;
                case 'bdo_reg':
                    finalValue = installmentValue * 1.03; // 3% interest
                    break;
                case 'bpi_reg':
                    finalValue = installmentValue * 1.035; // 3.5% interest
                    break;
                case 'bdo_zero':
                case 'homecredit':
                    finalValue = installmentValue;
                    break;
                default:
                    finalValue = totalValue;
            }
            
            // Only update if the value has changed
            const formattedFinal = finalValue.toFixed(2);
            if (finalTotal.textContent !== formattedFinal) {
                finalTotal.textContent = formattedFinal;
                
                // Update installment information
                updateInstallmentInfo(selectedMethod.value, finalValue);
            }
        } catch (error) {
            console.error('Error updating final total:', error);
        }
    }
    
    // Cache previous installment info to prevent unnecessary updates
    let previousPaymentMethod = '';
    let previousFinalPrice = 0;
    
    // Function to update installment information
    function updateInstallmentInfo(paymentMethod, finalPrice) {
        const installmentInfo = document.getElementById('installmentInfo');
        if (!installmentInfo) return;
        
        // Skip update if nothing changed
        if (paymentMethod === previousPaymentMethod && 
            Math.abs(finalPrice - previousFinalPrice) < 0.01) {
            return;
        }
        
        // Update cache
        previousPaymentMethod = paymentMethod;
        previousFinalPrice = finalPrice;
        
        // Format price helper function
        const formatPrice = (price) => {
            return price.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        };
        
        // Create content based on payment method
        let content = '';
        
        switch (paymentMethod) {
            case 'cash':
                content = `
                    <p>• Cash payment receives 5% discount</p>
                    <p>• GCash, PayMaya, and bank transfers accepted</p>
                    <p>• Same-day processing for cash payments</p>
                    <p>• All prices include VAT</p>
                `;
                break;
            case 'cc_straight':
                content = `
                    <p>• One-time payment using credit card</p>
                    <p>• All major credit cards accepted</p>
                    <p>• No additional fees</p>
                    <p>• All prices include VAT</p>
                `;
                break;
            case 'bdo_reg':
                content = `
                    <p>• Monthly payment: ₱${formatPrice(finalPrice / 12)} for 12 months</p>
                    <p>• 3% interest with BDO regular installment</p>
                    <p>• Minimum purchase of ₱3,000</p>
                    <p>• All prices include VAT</p>
                `;
                break;
            case 'bpi_reg':
                content = `
                    <p>• Monthly payment: ₱${formatPrice(finalPrice / 12)} for 12 months</p>
                    <p>• 3.5% interest with BPI regular installment</p>
                    <p>• Minimum purchase of ₱3,000</p>
                    <p>• All prices include VAT</p>
                `;
                break;
            case 'bdo_zero':
                content = `
                    <p>• Monthly payment: ₱${formatPrice(finalPrice / 6)} for 6 months</p>
                    <p>• 0% interest for 6 months with BDO credit card</p>
                    <p>• Minimum purchase of ₱5,000</p>
                    <p>• All prices include VAT</p>
                `;
                break;
            case 'homecredit':
                content = `
                    <p>• Monthly payment: ₱${formatPrice(finalPrice / 6)} for 6 months</p>
                    <p>• 0% interest for 6 months with Home Credit</p>
                    <p>• Valid government IDs required</p>
                    <p>• All prices include VAT</p>
                `;
                break;
        }
        
        // Only update DOM if content has changed
        if (installmentInfo.innerHTML !== content) {
            installmentInfo.innerHTML = content;
        }
    }
    
    // Debounced version of syncPrices to prevent flickering
    const debouncedSyncPrices = debounce(syncPrices, 300);
    
    // Set up event listeners for payment method changes
    const paymentMethods = document.querySelectorAll('input[name="paymentMethod"]');
    paymentMethods.forEach(method => {
        method.addEventListener('change', debouncedSyncPrices);
    });
    
    // Set up a MutationObserver to watch for changes in the build summary
    const buildSummary = document.querySelector('.bg-gray-900 .p-5');
    if (buildSummary) {
        const observer = new MutationObserver(debouncedSyncPrices);
        
        observer.observe(buildSummary, { 
            childList: true, 
            characterData: true, 
            subtree: true 
        });
    }
    
    // Initial sync (once)
    syncPrices();
});
