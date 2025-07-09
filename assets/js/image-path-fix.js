// Fix image paths at runtime to handle path inconsistencies
function fixImagePaths() {
    console.log('Running image path fix...');
    // Find all image elements
    const images = document.querySelectorAll('img');
    
    // Process each image
    images.forEach(img => {
        let src = img.getAttribute('src');
        if (src) {
            // Fix double category in path
            const categories = ['monitor', 'hard drive', 'cpu cooler', 'graphics card'];
            categories.forEach(category => {
                // Create regex pattern for capitalized category name
                const capitalizedCategory = category.split(' ')
                    .map(word => word.charAt(0).toUpperCase() + word.slice(1))
                    .join(' ');
                    
                const doublePathPattern = new RegExp(`components/${category}/${capitalizedCategory}/`, 'i');
                if (doublePathPattern.test(src)) {
                    console.log(`Fixing duplicate path for ${category} in: ${src}`);
                    src = src.replace(doublePathPattern, `components/${category}/`);
                }
            });
            
            // Fix backslashes using String.replace with string arguments
            while(src.includes('\\')) {
                src = src.split('\\').join('/');
            }
            
            // Update the image source if it changed
            if (src !== img.getAttribute('src')) {
                console.log(`Updated image path from ${img.getAttribute('src')} to ${src}`);
                img.setAttribute('src', src);
            }
        }
    });
}

// Add a mutation observer to fix paths for dynamically loaded images
function setupImagePathObserver() {
    const observer = new MutationObserver(mutations => {
        mutations.forEach(mutation => {
            if (mutation.addedNodes && mutation.addedNodes.length) {
                let hasNewImages = false;
                mutation.addedNodes.forEach(node => {
                    if (node.tagName === 'IMG') {
                        hasNewImages = true;
                    } else if (node.querySelectorAll) {
                        const images = node.querySelectorAll('img');
                        if (images.length > 0) {
                            hasNewImages = true;
                        }
                    }
                });
                
                if (hasNewImages) {
                    setTimeout(fixImagePaths, 50);
                }
            }
        });
    });
    
    // Start observing the document
    observer.observe(document.body, { childList: true, subtree: true });
}

// Initialize when the DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Run image path fix on page load
    fixImagePaths();
    
    // Setup observer for dynamic content
    setupImagePathObserver();
    
    // Add event listener to fix paths after component selection changes
    document.querySelectorAll('select').forEach(select => {
        select.addEventListener('change', function() {
            setTimeout(fixImagePaths, 150);
        });
    });
});
