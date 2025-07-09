/**
 * Standalone Component Selector Integration
 * This script redirects Monitor and Hard Drive selection to a separate page
 * and handles the selection when returning to the PC Builder
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('Standalone selector integration loaded');
    
    // Check if we have a selection from the standalone page
    checkForSelection();
    
    // Override the openComponentSelector function for Monitor and Hard Drive
    const originalOpenComponentSelector = window.openComponentSelector;
    
    window.openComponentSelector = function(category, label, icon, dbCategory) {
        // For Monitor and Hard Drive, redirect to the standalone page
        if (category === 'Monitor' || category === 'Hard Drive') {
            console.log(`Redirecting to standalone selector for ${category}`);
            window.location.href = `../select_component.php?category=${category}&return=${encodeURIComponent(window.location.pathname)}`;
            return;
        }
        
        // For other categories, use the original function
        originalOpenComponentSelector(category, label, icon, dbCategory);
    };
});

// Function to check for selection from standalone page
function checkForSelection() {
    const selectionData = localStorage.getItem('selected_component');
    if (!selectionData) return;
    
    try {
        const selection = JSON.parse(selectionData);
        console.log('Found selection from standalone page:', selection);
        
        // Only process recent selections (within the last minute)
        const selectionTime = new Date(selection.timestamp).getTime();
        const currentTime = new Date().getTime();
        const timeDiff = currentTime - selectionTime;
        
        if (timeDiff > 60000) {
            console.log('Selection is too old, ignoring');
            localStorage.removeItem('selected_component');
            return;
        }
        
        // Apply the selection using the utility function
        setTimeout(function() {
            if (window.componentUtils && window.componentUtils.applyComponentSelection) {
                window.componentUtils.applyComponentSelection(selection.category, selection.name);
                window.componentUtils.showSuccessMessage(selection.category, selection.name);
            }
            
            // Clear the selection from localStorage
            localStorage.removeItem('selected_component');
        }, 500);
    } catch (error) {
        console.error('Error processing selection:', error);
        localStorage.removeItem('selected_component');
    }
}

// Function to apply the selection to the PC Builder
function applySelection(category, name) {
    console.log(`Applying selection: ${category} - ${name}`);
    
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
            
            // Show a success message
            showSuccessMessage(category, name);
            
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
        
        // Show a success message
        showSuccessMessage(category, select.options[1].value);
    }
}

// Function to show a success message
function showSuccessMessage(category, name) {
    // Create a notification element
    const notification = document.createElement('div');
    notification.className = 'fixed top-4 right-4 bg-green-600 text-white px-4 py-3 rounded-lg shadow-lg z-50 animate-bounce';
    notification.innerHTML = `
        <div class="flex items-center">
            <i data-lucide="check-circle" class="w-5 h-5 mr-2"></i>
            <span>${category} selected: ${name}</span>
        </div>
    `;
    
    // Add to document
    document.body.appendChild(notification);
    
    // Initialize icon
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
    
    // Remove after 3 seconds
    setTimeout(function() {
        notification.classList.add('opacity-0');
        notification.style.transition = 'opacity 0.5s ease';
        
        setTimeout(function() {
            notification.remove();
        }, 500);
    }, 3000);
}
