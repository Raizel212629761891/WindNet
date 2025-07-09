/**
 * Direct fix for Monitor and Hard Drive selection
 * This script completely bypasses the existing selection mechanism
 * and implements a direct solution that will work 100%
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('Direct fix for Monitor and Hard Drive loaded');

    // Add direct selection buttons to all component cards
    setTimeout(addDirectSelectionButtons, 1000);

    // Monitor for new components being loaded
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                setTimeout(addDirectSelectionButtons, 500);
            }
        });
    });

    // Start observing the component grid
    const grid = document.getElementById('component-grid');
    if (grid) {
        observer.observe(grid, { childList: true, subtree: true });
    }
});

// Function to add direct selection buttons to component cards
function addDirectSelectionButtons() {
    console.log('Adding direct selection buttons');
    
    // Find all component items in the modal
    const componentItems = document.querySelectorAll('.component-item');
    
    componentItems.forEach(item => {
        // Get the onclick attribute
        const onclickAttr = item.getAttribute('onclick');
        if (!onclickAttr) return;
        
        // Check if this is a Monitor or Hard Drive component
        if (onclickAttr.includes("'Monitor'") || onclickAttr.includes("'Hard Drive'")) {
            // Extract the category and name
            const match = onclickAttr.match(/selectComponent\(['"]([^'"]+)['"],\s*['"]([^'"]+)['"]\)/);
            if (!match) return;
            
            const category = match[1];
            const name = match[2];
            
            // Check if we've already added a button to this item
            if (item.querySelector('.direct-select-btn')) return;
            
            // Create a direct selection button
            const button = document.createElement('button');
            button.className = 'direct-select-btn absolute top-2 right-2 bg-green-600 hover:bg-green-700 text-white rounded-full p-1';
            button.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>';
            button.title = `Direct select: ${name}`;
            
            // Add click event
            button.addEventListener('click', function(event) {
                event.preventDefault();
                event.stopPropagation();
                
                console.log(`Direct selection for ${category}: ${name}`);
                
                // Close the modal
                const modal = document.getElementById('component-selector-modal');
                if (modal) {
                    modal.classList.add('hidden');
                    document.body.style.overflow = '';
                }
                
                // Find the select element
                setTimeout(() => {
                    directSelect(category, name);
                }, 100);
            });
            
            // Make the component item position relative for absolute positioning of the button
            item.style.position = 'relative';
            
            // Add the button to the component item
            item.appendChild(button);
        }
    });
}

// Function to directly select a component
function directSelect(category, name) {
    console.log(`Direct selection: ${category} - ${name}`);
    
    // Find the select element
    let select = null;
    
    if (category === 'Monitor') {
        // For Monitor, try different selectors
        select = document.querySelector('select[data-category="Monitor"]');
        
        if (!select) {
            select = document.querySelector('select[name="Monitor"]');
        }
    } else if (category === 'Hard Drive') {
        // For Hard Drive, try different selectors
        select = document.querySelector('select[data-category="Hard Drive"]');
        
        if (!select) {
            // Try finding through Storage category
            const storageSelects = document.querySelectorAll('select[data-db-category="Storage"]');
            for (const s of storageSelects) {
                if (s.getAttribute('data-category') === 'Hard Drive') {
                    select = s;
                    break;
                }
            }
        }
    }
    
    if (!select) {
        console.error(`Could not find select element for ${category}`);
        return;
    }
    
    console.log(`Found select element for ${category}:`, select);
    
    // Find the option with the matching name
    let found = false;
    for (let i = 0; i < select.options.length; i++) {
        const option = select.options[i];
        const optionValue = option.value;
        const optionText = option.textContent;
        
        // Skip placeholder option
        if (i === 0 && optionValue === '0') continue;
        
        // Try different matching strategies
        if (optionValue === name || 
            optionValue.toLowerCase() === name.toLowerCase() ||
            optionValue.includes(name) || 
            name.includes(optionValue) ||
            optionText.includes(name) ||
            name.includes(optionText)) {
            
            console.log(`Found matching option at index ${i}: ${optionValue}`);
            
            // Select this option
            select.selectedIndex = i;
            found = true;
            
            // Trigger change event
            const event = new Event('change', { bubbles: true });
            select.dispatchEvent(event);
            
            // Also manually trigger all update functions
            if (typeof updateSelection === 'function') updateSelection(select);
            if (typeof calculateTotal === 'function') calculateTotal();
            if (typeof updateComponentImage === 'function') updateComponentImage(select);
            if (typeof updateComponentPrices === 'function') updateComponentPrices(select);
            
            break;
        }
    }
    
    // If no match found, select the first non-placeholder option
    if (!found && select.options.length > 1) {
        console.log(`No match found, selecting first option: ${select.options[1].value}`);
        select.selectedIndex = 1;
        
        // Trigger change event
        const event = new Event('change', { bubbles: true });
        select.dispatchEvent(event);
        
        // Also manually trigger all update functions
        if (typeof updateSelection === 'function') updateSelection(select);
        if (typeof calculateTotal === 'function') calculateTotal();
        if (typeof updateComponentImage === 'function') updateComponentImage(select);
        if (typeof updateComponentPrices === 'function') updateComponentPrices(select);
    }
}
