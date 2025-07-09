/**
 * Fix for Monitor and Hard Drive modal parameters
 * This script fixes the parameter passing issue when opening the component selector modal
 */

// Wait for page to load
document.addEventListener('DOMContentLoaded', function() {
    console.log('Modal parameter fix loaded');
    
    // Override the openComponentSelector function to ensure all parameters are passed correctly
    const originalOpenComponentSelector = window.openComponentSelector;
    
    window.openComponentSelector = function(category, label, icon, dbCategory) {
        console.log(`Opening component selector with params:`, { category, label, icon, dbCategory });
        
        // Fix missing parameters for Monitor and Hard Drive
        if (category === 'Monitor' && !label) {
            label = 'Monitor';
            icon = icon || 'monitor';
            dbCategory = dbCategory || 'Monitor';
            console.log('Fixed Monitor parameters');
        }
        
        if (category === 'Hard Drive' && !label) {
            label = 'Hard Drive';
            icon = icon || 'hard-drive';
            dbCategory = dbCategory || 'Storage';
            console.log('Fixed Hard Drive parameters');
        }
        
        // Call the original function with the fixed parameters
        originalOpenComponentSelector(category, label, icon, dbCategory);
    };
    
    // Fix the click handlers on "Select" buttons for Monitor and Hard Drive
    function fixSelectButtons() {
        console.log('Fixing select buttons for Monitor and Hard Drive');
        
        // Find all select buttons
        const selectButtons = document.querySelectorAll('.select-component-btn');
        
        selectButtons.forEach(button => {
            const category = button.getAttribute('data-category');
            
            // Only fix Monitor and Hard Drive buttons
            if (category === 'Monitor' || category === 'Hard Drive') {
                console.log(`Found ${category} select button:`, button);
                
                // Get the original onclick attribute
                const originalOnclick = button.getAttribute('onclick');
                
                // If it's missing parameters, fix it
                if (originalOnclick && originalOnclick.includes(`openComponentSelector('${category}')`)) {
                    let newOnclick;
                    
                    if (category === 'Monitor') {
                        newOnclick = `openComponentSelector('Monitor', 'Monitor', 'monitor', 'Monitor')`;
                    } else if (category === 'Hard Drive') {
                        newOnclick = `openComponentSelector('Hard Drive', 'Hard Drive', 'hard-drive', 'Storage')`;
                    }
                    
                    if (newOnclick) {
                        console.log(`Fixing onclick for ${category} button: ${originalOnclick} -> ${newOnclick}`);
                        button.setAttribute('onclick', newOnclick);
                        
                        // Also add a direct event listener as a backup
                        button.addEventListener('click', function(event) {
                            event.preventDefault();
                            event.stopPropagation();
                            
                            if (category === 'Monitor') {
                                openComponentSelector('Monitor', 'Monitor', 'monitor', 'Monitor');
                            } else if (category === 'Hard Drive') {
                                openComponentSelector('Hard Drive', 'Hard Drive', 'hard-drive', 'Storage');
                            }
                        });
                    }
                }
            }
        });
    }
    
    // Run the fix after a short delay to ensure all elements are loaded
    setTimeout(fixSelectButtons, 1000);
});
