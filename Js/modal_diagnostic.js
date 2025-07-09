/**
 * Modal Diagnostic Tool
 * This script analyzes the component modals to identify differences between
 * working categories and problematic ones (Monitor and Hard Drive)
 */

// Wait for page to load
document.addEventListener('DOMContentLoaded', function() {
    console.log('Modal Diagnostic Tool loaded');
    
    // Create diagnostic panel
    createDiagnosticPanel();
    
    // Monitor modal opening
    const originalOpenComponentSelector = window.openComponentSelector;
    window.openComponentSelector = function(category) {
        console.log(`%c[DIAGNOSTIC] Opening modal for category: ${category}`, 'background:#3b82f6;color:white;padding:3px 6px;border-radius:3px;');
        
        // Call original function
        originalOpenComponentSelector(category);
        
        // After modal is opened, analyze it
        setTimeout(() => {
            analyzeModal(category);
        }, 500);
    };
    
    // Monitor component clicks
    document.addEventListener('click', function(event) {
        // Find the component item that was clicked
        const componentItem = event.target.closest('.component-item');
        if (!componentItem) return;
        
        // Get the onclick attribute
        const onclickAttr = componentItem.getAttribute('onclick');
        if (!onclickAttr) return;
        
        // Log the click
        console.log(`%c[DIAGNOSTIC] Component clicked: ${onclickAttr}`, 'background:#10b981;color:white;padding:3px 6px;border-radius:3px;');
        
        // For Monitor and Hard Drive, capture more details
        if (onclickAttr.includes("'Monitor'") || onclickAttr.includes("'Hard Drive'")) {
            const match = onclickAttr.match(/selectComponent\(['"]([^'"]+)['"],\s*['"]([^'"]+)['"]\)/);
            if (match) {
                const category = match[1];
                const name = match[2];
                
                // Log detailed info
                console.log(`%c[DIAGNOSTIC] ${category} component details:`, 'background:#f59e0b;color:white;padding:3px 6px;border-radius:3px;');
                console.log('- Name:', name);
                console.log('- Element:', componentItem);
                console.log('- Onclick:', onclickAttr);
                
                // Add to diagnostic panel
                addDiagnosticLog(`Clicked ${category} component: ${name}`);
                
                // Find the corresponding select element
                findSelectElement(category, name);
            }
        }
    }, true);
});

// Function to create diagnostic panel
function createDiagnosticPanel() {
    const panel = document.createElement('div');
    panel.id = 'diagnostic-panel';
    panel.style.cssText = `
        position: fixed;
        bottom: 0;
        right: 0;
        width: 400px;
        height: 300px;
        background: rgba(17, 24, 39, 0.95);
        color: white;
        z-index: 9999;
        border-top-left-radius: 8px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        font-family: monospace;
        font-size: 12px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
        transition: all 0.3s ease;
    `;
    
    // Header
    const header = document.createElement('div');
    header.style.cssText = `
        padding: 8px 12px;
        background: #2563eb;
        display: flex;
        justify-content: space-between;
        align-items: center;
        cursor: pointer;
    `;
    header.innerHTML = `
        <span style="font-weight: bold;">Component Selection Diagnostic</span>
        <div>
            <button id="diagnostic-clear" style="margin-right: 8px; padding: 2px 6px; background: #1e40af; border: none; border-radius: 3px; cursor: pointer;">Clear</button>
            <button id="diagnostic-toggle" style="padding: 2px 6px; background: #1e40af; border: none; border-radius: 3px; cursor: pointer;">Hide</button>
        </div>
    `;
    
    // Log container
    const logContainer = document.createElement('div');
    logContainer.id = 'diagnostic-log';
    logContainer.style.cssText = `
        flex: 1;
        overflow-y: auto;
        padding: 8px 12px;
        background: #111827;
    `;
    
    // Actions container
    const actionsContainer = document.createElement('div');
    actionsContainer.style.cssText = `
        padding: 8px 12px;
        background: #1f2937;
        display: flex;
        gap: 8px;
    `;
    actionsContainer.innerHTML = `
        <button id="test-processor" style="padding: 4px 8px; background: #2563eb; border: none; border-radius: 3px; cursor: pointer;">Test Processor</button>
        <button id="test-monitor" style="padding: 4px 8px; background: #2563eb; border: none; border-radius: 3px; cursor: pointer;">Test Monitor</button>
        <button id="test-harddrive" style="padding: 4px 8px; background: #2563eb; border: none; border-radius: 3px; cursor: pointer;">Test Hard Drive</button>
    `;
    
    // Assemble panel
    panel.appendChild(header);
    panel.appendChild(logContainer);
    panel.appendChild(actionsContainer);
    
    // Add to document
    document.body.appendChild(panel);
    
    // Add event listeners
    document.getElementById('diagnostic-toggle').addEventListener('click', function() {
        const panel = document.getElementById('diagnostic-panel');
        const isHidden = panel.style.height === '30px';
        
        if (isHidden) {
            panel.style.height = '300px';
            this.textContent = 'Hide';
        } else {
            panel.style.height = '30px';
            this.textContent = 'Show';
        }
    });
    
    document.getElementById('diagnostic-clear').addEventListener('click', function() {
        document.getElementById('diagnostic-log').innerHTML = '';
    });
    
    // Test buttons
    document.getElementById('test-processor').addEventListener('click', function() {
        addDiagnosticLog('Testing Processor selection...');
        openComponentSelector('Processor');
    });
    
    document.getElementById('test-monitor').addEventListener('click', function() {
        addDiagnosticLog('Testing Monitor selection...');
        openComponentSelector('Monitor');
    });
    
    document.getElementById('test-harddrive').addEventListener('click', function() {
        addDiagnosticLog('Testing Hard Drive selection...');
        openComponentSelector('Hard Drive');
    });
    
    // Initial log
    addDiagnosticLog('Diagnostic panel initialized');
}

// Function to add log to diagnostic panel
function addDiagnosticLog(message) {
    const logContainer = document.getElementById('diagnostic-log');
    if (!logContainer) return;
    
    const timestamp = new Date().toLocaleTimeString();
    const logEntry = document.createElement('div');
    logEntry.style.cssText = `
        margin-bottom: 4px;
        border-left: 3px solid #3b82f6;
        padding-left: 8px;
    `;
    logEntry.innerHTML = `<span style="color: #9ca3af;">[${timestamp}]</span> ${message}`;
    
    logContainer.appendChild(logEntry);
    logContainer.scrollTop = logContainer.scrollHeight;
    
    // Also log to console
    console.log(`[DIAGNOSTIC] ${message}`);
}

// Function to analyze modal
function analyzeModal(category) {
    const modal = document.getElementById('component-selector-modal');
    if (!modal || modal.classList.contains('hidden')) {
        addDiagnosticLog(`Error: Modal for ${category} not found or hidden`);
        return;
    }
    
    addDiagnosticLog(`Analyzing modal for ${category}...`);
    
    // Check modal title
    const modalTitle = document.getElementById('modal-title');
    if (modalTitle) {
        addDiagnosticLog(`Modal title: "${modalTitle.textContent}"`);
    }
    
    // Count components in grid
    const grid = document.getElementById('component-grid');
    if (grid) {
        const components = grid.querySelectorAll('.component-item');
        addDiagnosticLog(`Found ${components.length} components in grid`);
        
        // Check first component
        if (components.length > 0) {
            const firstComponent = components[0];
            const onclick = firstComponent.getAttribute('onclick');
            addDiagnosticLog(`First component onclick: ${onclick}`);
            
            // Check if the onclick handler is correct
            if (onclick) {
                if (onclick.includes('selectComponent')) {
                    addDiagnosticLog(`✅ Onclick uses selectComponent function`);
                } else if (onclick.includes('directComponentSelect')) {
                    addDiagnosticLog(`⚠️ Onclick uses directComponentSelect function`);
                } else {
                    addDiagnosticLog(`❌ Unknown onclick function: ${onclick}`);
                }
            }
        }
    }
    
    // Find corresponding select element
    findSelectElement(category);
}

// Function to find the select element for a category
function findSelectElement(category, componentName = null) {
    addDiagnosticLog(`Finding select element for ${category}${componentName ? ': ' + componentName : ''}...`);
    
    let select = null;
    let foundMethod = '';
    
    // Method 1: Try by name attribute
    select = document.querySelector(`select[name="${category}"]`);
    if (select) foundMethod = 'name attribute';
    
    // Method 2: Try by data-category attribute
    if (!select) {
        select = document.querySelector(`select[data-category="${category}"]`);
        if (select) foundMethod = 'data-category attribute';
    }
    
    // Method 3: Try by data-category attribute case-insensitive
    if (!select) {
        const allSelects = document.querySelectorAll('select[data-category]');
        for (const s of allSelects) {
            const selectCategory = s.getAttribute('data-category');
            if (selectCategory && selectCategory.toLowerCase() === category.toLowerCase()) {
                select = s;
                foundMethod = 'case-insensitive data-category';
                break;
            }
        }
    }
    
    // Method 4: Special case for Hard Drive
    if (!select && category === 'Hard Drive') {
        const storageSelects = document.querySelectorAll('select[data-db-category="Storage"]');
        for (const s of storageSelects) {
            if (s.getAttribute('data-category') === 'Hard Drive') {
                select = s;
                foundMethod = 'data-db-category="Storage" + data-category="Hard Drive"';
                break;
            }
        }
    }
    
    if (!select) {
        addDiagnosticLog(`❌ Could not find select element for ${category}`);
        return;
    }
    
    addDiagnosticLog(`✅ Found select element using ${foundMethod}`);
    
    // Check options
    const options = select.options;
    addDiagnosticLog(`Select has ${options.length} options`);
    
    // If we have a component name, try to find it
    if (componentName) {
        let found = false;
        for (let i = 0; i < options.length; i++) {
            const option = options[i];
            const optionValue = option.value;
            const optionText = option.textContent;
            
            // Skip placeholder option
            if (i === 0 && optionValue === '0') continue;
            
            if (optionValue === componentName || 
                optionValue.toLowerCase() === componentName.toLowerCase() ||
                optionValue.includes(componentName) || 
                componentName.includes(optionValue) ||
                optionText.includes(componentName) ||
                componentName.includes(optionText)) {
                
                addDiagnosticLog(`✅ Found matching option at index ${i}: ${optionValue}`);
                found = true;
                
                // Try to select this option
                addDiagnosticLog(`Attempting to select option...`);
                
                try {
                    // Store original value
                    const originalIndex = select.selectedIndex;
                    
                    // Select new option
                    select.selectedIndex = i;
                    
                    // Trigger change event
                    const event = new Event('change', { bubbles: true });
                    select.dispatchEvent(event);
                    
                    addDiagnosticLog(`Changed selection from index ${originalIndex} to ${i}`);
                    
                    // Check if any update functions exist and call them
                    if (typeof updateSelection === 'function') {
                        updateSelection(select);
                        addDiagnosticLog(`Called updateSelection()`);
                    }
                    if (typeof calculateTotal === 'function') {
                        calculateTotal();
                        addDiagnosticLog(`Called calculateTotal()`);
                    }
                    if (typeof updateComponentImage === 'function') {
                        updateComponentImage(select);
                        addDiagnosticLog(`Called updateComponentImage()`);
                    }
                    if (typeof updateComponentPrices === 'function') {
                        updateComponentPrices(select);
                        addDiagnosticLog(`Called updateComponentPrices()`);
                    }
                } catch (error) {
                    addDiagnosticLog(`❌ Error selecting option: ${error.message}`);
                }
                
                break;
            }
        }
        
        if (!found) {
            addDiagnosticLog(`❌ Could not find option matching "${componentName}"`);
        }
    }
}
