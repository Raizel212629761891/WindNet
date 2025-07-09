/**
 * Wind Net PC Builder - Component Utilities
 * This file contains shared functionality for component selection and management
 */

// Global variables
if (typeof window.componentQuantities === 'undefined') {
    window.componentQuantities = {};
}

/**
 * Get selected components
 * @returns {Object} - Object containing selected components
 */
function getSelectedComponents() {
    const selects = document.querySelectorAll('#pcBuilderForm select');
    const selectedComponents = {};
    
    selects.forEach(select => {
        const category = select.getAttribute('data-category').toLowerCase();
        const selectedOption = select.selectedOptions[0];
        
        if (selectedOption.value !== '0') {
            selectedComponents[category] = {
                name: selectedOption.value,
                specs: selectedOption.getAttribute('data-specs') || ''
            };
        } else {
            selectedComponents[category] = null;
        }
    });
    
    return selectedComponents;
}

/**
 * Find a select element for a given category
 * @param {string} category - The component category
 * @returns {HTMLSelectElement|null} - The found select element or null
 */
function findSelectElement(category) {
    let select = null;
    
    // Try different selectors in order of preference
    const selectors = [
        `select[data-category="${category}"]`,
        `select[name="${category}"]`,
        `select[data-db-category="${category}"]`
    ];
    
    for (const selector of selectors) {
        select = document.querySelector(selector);
        if (select) break;
    }
    
    // Special case for Hard Drive
    if (!select && category === 'Hard Drive') {
        const storageSelects = document.querySelectorAll('select[data-db-category="Storage"]');
        for (const s of storageSelects) {
            if (s.getAttribute('data-category') === 'Hard Drive') {
                select = s;
                break;
            }
        }
    }
    
    return select;
}

/**
 * Find a matching option in a select element
 * @param {HTMLSelectElement} select - The select element
 * @param {string} name - The component name to match
 * @returns {number} - The index of the matching option or -1
 */
function findMatchingOption(select, name) {
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
            return i;
        }
    }
    return -1;
}

/**
 * Select an option in a select element and update related UI
 * @param {HTMLSelectElement} select - The select element
 * @param {number} index - The index of the option to select
 */
function selectOptionAndUpdate(select, index) {
    if (!select || index < 0 || index >= select.options.length) return;
    
    // Select the option
    select.selectedIndex = index;
    
    // Trigger change event
    const event = new Event('change', { bubbles: true });
    select.dispatchEvent(event);
    
    // Update related UI elements
    if (typeof updateSelection === 'function') updateSelection(select);
    if (typeof calculateTotal === 'function') calculateTotal();
    if (typeof updateComponentImage === 'function') updateComponentImage(select);
    if (typeof updateComponentPrices === 'function') updateComponentPrices(select);
}

/**
 * Apply a component selection
 * @param {string} category - The component category
 * @param {string} name - The component name
 * @returns {boolean} - Whether the selection was successful
 */
function applyComponentSelection(category, name) {
    console.log(`Applying selection: ${category} - ${name}`);
    
    const select = findSelectElement(category);
    if (!select) {
        console.error(`Could not find select element for ${category}`);
        return false;
    }
    
    const matchingIndex = findMatchingOption(select, name);
    if (matchingIndex >= 0) {
        selectOptionAndUpdate(select, matchingIndex);
        return true;
    }
    
    // If no match found, select the first non-placeholder option
    if (select.options.length > 1) {
        console.log(`No match found, selecting first option: ${select.options[1].value}`);
        selectOptionAndUpdate(select, 1);
        return true;
    }
    
    return false;
}

/**
 * Show a success message
 * @param {string} category - The component category
 * @param {string} name - The component name
 */
function showSuccessMessage(category, name) {
    const notification = document.createElement('div');
    notification.className = 'fixed top-4 right-4 bg-green-600 text-white px-4 py-3 rounded-lg shadow-lg z-50 animate-bounce';
    notification.innerHTML = `
        <div class="flex items-center">
            <i data-lucide="check-circle" class="w-5 h-5 mr-2"></i>
            <span>${category} selected: ${name}</span>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
    
    setTimeout(() => {
        notification.classList.add('opacity-0');
        notification.style.transition = 'opacity 0.5s ease';
        
        setTimeout(() => {
            notification.remove();
        }, 500);
    }, 3000);
}

/**
 * Close the component selector modal
 */
function closeComponentSelector() {
    const modal = document.getElementById('component-selector-modal');
    if (modal) {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }
}

/**
 * Update component quantity
 * @param {string} category - The component category
 * @param {number} change - The amount to change the quantity by
 */
function updateQuantity(category, change) {
    const quantityInput = document.getElementById(`quantity-${category}`);
    if (!quantityInput) return;
    
    let currentValue = parseInt(quantityInput.value) || 1;
    let newValue = currentValue + change;
    
    // Ensure quantity is between 1 and 99
    newValue = Math.max(1, Math.min(99, newValue));
    quantityInput.value = newValue;
    
    // Store the quantity
    window.componentQuantities[category] = newValue;
    
    // Update totals and prices
    if (typeof calculateTotal === 'function') calculateTotal();
    
    // Update the component's individual price display
    const categorySelect = document.querySelector(`select[data-category="${category}"]`);
    if (categorySelect && typeof updateComponentPrices === 'function') {
        updateComponentPrices(categorySelect);
    }
}

/**
 * Validate quantity input
 * @param {HTMLInputElement} input - The quantity input element
 */
function validateQuantity(input) {
    let value = parseInt(input.value) || 1;
    value = Math.max(1, Math.min(99, value));
    input.value = value;
    
    const category = input.getAttribute('data-category');
    window.componentQuantities[category] = value;
}

// Export functions for use in other files
window.componentUtils = {
    findSelectElement,
    findMatchingOption,
    selectOptionAndUpdate,
    applyComponentSelection,
    showSuccessMessage,
    closeComponentSelector,
    updateQuantity,
    validateQuantity,
    getSelectedComponents,
    componentQuantities: window.componentQuantities
}; 