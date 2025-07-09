/**
 * Wind Net PC Builder - Component Selection Fix
 * 
 * This script fixes selection issues for Monitor and Hard Drive components
 * by directly patching the selection functions in the PC Builder page.
 */

// Wait for the page to load
document.addEventListener('DOMContentLoaded', function() {
    console.log('Component Selection Fix loaded');
    
    // Fix component selection in the modal
    fixComponentSelection();
});

// Function to fix component selection
function fixComponentSelection() {
    console.log('Applying component selection fixes');
    
    // 1. Fix the click handlers on component items in the modal
    document.addEventListener('click', function(event) {
        // Find the closest component-item ancestor
        const componentItem = event.target.closest('.component-item');
        if (!componentItem) return;
        
        // Get the onclick attribute
        const onclickAttr = componentItem.getAttribute('onclick');
        if (!onclickAttr) return;
        
        // Check if this is a Monitor or Hard Drive component
        if (onclickAttr.includes("'Monitor'") || onclickAttr.includes("'Hard Drive'")) {
            // Prevent the default onclick behavior
            event.preventDefault();
            event.stopPropagation();
            
            // Extract the category and name from the onclick attribute
            const match = onclickAttr.match(/\('([^']+)',\s*'([^']+)'/);
            if (match) {
                const category = match[1];
                const name = match[2];
                
                console.log(`Direct selection: ${category} - ${name}`);
                
                // Close the modal
                if (window.componentUtils && window.componentUtils.closeComponentSelector) {
                    window.componentUtils.closeComponentSelector();
                }
                
                // Apply the selection using the utility function
                setTimeout(function() {
                    if (window.componentUtils && window.componentUtils.applyComponentSelection) {
                        window.componentUtils.applyComponentSelection(category, name);
                        window.componentUtils.showSuccessMessage(category, name);
                    }
                }, 100);
            }
        }
    }, true); // Use capture phase to intercept before the original handler
}
