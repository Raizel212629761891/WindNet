/**
 * Fix for Monitor and Hard Drive component selection
 * This script directly fixes the selection issues for these specific categories
 */

// Wait for page to load
document.addEventListener('DOMContentLoaded', function() {
    console.log('Monitor and Hard Drive fix loaded');
    
    // Add click event listeners to all component items in the modal
    document.addEventListener('click', function(event) {
        // Find the component item that was clicked
        const componentItem = event.target.closest('.component-item');
        if (!componentItem) return;
        
        // Get the onclick attribute to check if it's a Monitor or Hard Drive
        const onclickAttr = componentItem.getAttribute('onclick');
        if (!onclickAttr) return;
        
        // Only handle Monitor and Hard Drive components
        if (onclickAttr.includes("'Monitor'") || onclickAttr.includes("'Hard Drive'")) {
            // Prevent default behavior
            event.preventDefault();
            event.stopPropagation();
            
            // Extract category and name from the onclick attribute
            const match = onclickAttr.match(/selectComponent\(['"]([^'"]+)['"],\s*['"]([^'"]+)['"]\)/);
            if (!match) return;
            
            const category = match[1];
            const name = match[2];
            
            console.log(`Special handling for ${category}: ${name}`);
            
            // Close the modal using utility function
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
    }, true); // Use capture phase to intercept before original handler
});
