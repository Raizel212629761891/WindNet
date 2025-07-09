<?php
// This is a test file to fix the quantity calculation issue
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quantity Fix</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background-color: #111827; color: white; font-family: Arial, sans-serif; }
    </style>
</head>
<body class="p-8">
    <h1 class="text-2xl font-bold mb-6">Quantity Fix</h1>
    
    <div class="bg-gray-800 p-6 rounded-lg mb-6">
        <h2 class="text-xl mb-4">Component</h2>
        
        <div class="flex items-center justify-between mb-4">
            <span>Price per unit:</span>
            <span id="unit-price">₱1000.00</span>
        </div>
        
        <div class="flex items-center justify-between mb-4">
            <span>Quantity:</span>
            <div class="flex items-center space-x-2">
                <button id="decrease" class="w-8 h-8 bg-gray-700 rounded flex items-center justify-center">-</button>
                <input id="quantity" type="number" value="1" min="1" max="99" class="w-16 h-8 bg-gray-900 border border-gray-700 rounded text-center">
                <button id="increase" class="w-8 h-8 bg-gray-700 rounded flex items-center justify-center">+</button>
            </div>
        </div>
        
        <div class="flex items-center justify-between font-bold">
            <span>Total:</span>
            <span id="total-price">₱1000.00</span>
        </div>
    </div>
    
    <div class="bg-blue-900 p-4 rounded-lg">
        <h3 class="font-bold mb-2">JavaScript Code:</h3>
        <pre class="bg-gray-900 p-4 rounded overflow-x-auto text-xs"><code>
// Store the unit price
const unitPrice = 1000;

// Function to update quantity
function updateQuantity(change) {
    const quantityInput = document.getElementById('quantity');
    let currentValue = parseInt(quantityInput.value) || 1;
    let newValue = currentValue + change;
    
    // Ensure quantity is between 1 and 99
    newValue = Math.max(1, Math.min(99, newValue));
    quantityInput.value = newValue;
    
    // Update total
    calculateTotal();
}

// Function to calculate total
function calculateTotal() {
    const quantity = parseInt(document.getElementById('quantity').value) || 1;
    const total = unitPrice * quantity;
    
    // Format and display the total
    document.getElementById('total-price').textContent = 
        `₱${total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
    
    console.log(`Quantity: ${quantity}, Unit Price: ${unitPrice}, Total: ${total}`);
}

// Add event listeners
document.getElementById('decrease').addEventListener('click', () => updateQuantity(-1));
document.getElementById('increase').addEventListener('click', () => updateQuantity(1));
document.getElementById('quantity').addEventListener('input', calculateTotal);

// Initial calculation
calculateTotal();
        </code></pre>
    </div>
    
    <script>
        // Store the unit price
        const unitPrice = 1000;
        
        // Function to update quantity
        function updateQuantity(change) {
            const quantityInput = document.getElementById('quantity');
            let currentValue = parseInt(quantityInput.value) || 1;
            let newValue = currentValue + change;
            
            // Ensure quantity is between 1 and 99
            newValue = Math.max(1, Math.min(99, newValue));
            quantityInput.value = newValue;
            
            // Update total
            calculateTotal();
        }
        
        // Function to calculate total
        function calculateTotal() {
            const quantity = parseInt(document.getElementById('quantity').value) || 1;
            const total = unitPrice * quantity;
            
            // Format and display the total
            document.getElementById('total-price').textContent = 
                `₱${total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
            
            console.log(`Quantity: ${quantity}, Unit Price: ${unitPrice}, Total: ${total}`);
        }
        
        // Add event listeners
        document.getElementById('decrease').addEventListener('click', () => updateQuantity(-1));
        document.getElementById('increase').addEventListener('click', () => updateQuantity(1));
        document.getElementById('quantity').addEventListener('input', calculateTotal);
        
        // Initial calculation
        calculateTotal();
    </script>
</body>
</html>
