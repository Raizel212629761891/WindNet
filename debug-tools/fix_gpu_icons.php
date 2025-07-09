<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>GPU Icon Fixer</h1>";

// Directory to scan
$rootDir = __DIR__;

// Find all PHP, HTML, and JS files
$files = [];
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($rootDir));
foreach ($iterator as $file) {
    if ($file->isFile()) {
        $extension = $file->getExtension();
        if (in_array(strtolower($extension), ['php', 'html', 'js'])) {
            $files[] = $file->getPathname();
        }
    }
}

echo "<p>Found " . count($files) . " files to check.</p>";
echo "<ul>";

$replacementCount = 0;
$fileCount = 0;

// Process each file
foreach ($files as $file) {
    $content = file_get_contents($file);
    $originalContent = $content;
    
    // Replace data-lucide="gpu" with data-lucide="monitor"
    $newContent = str_replace('data-lucide="gpu"', 'data-lucide="monitor"', $content);
    
    // Also replace any JavaScript references
    $newContent = str_replace("'gpu'", "'monitor'", $newContent);
    
    // Check if content was modified
    if ($newContent !== $originalContent) {
        // Count replacements
        $count = substr_count($originalContent, 'data-lucide="gpu"') + 
                 substr_count($originalContent, "'gpu'") - 
                 substr_count($originalContent, "'gpu'", "'monitor'");
        
        // Update the file
        file_put_contents($file, $newContent);
        
        echo "<li>Updated file: " . htmlspecialchars(str_replace($rootDir, '', $file)) . " - Replaced $count occurrences</li>";
        $replacementCount += $count;
        $fileCount++;
    }
}

echo "</ul>";
echo "<p>Fixed $replacementCount GPU icon references across $fileCount files.</p>";

// Fix the SyntaxError in pc-builder1.php
$pcBuilderFile = $rootDir . '/includes/pc-builder1.php';
if (file_exists($pcBuilderFile)) {
    echo "<h2>Checking for SyntaxError in pc-builder1.php</h2>";
    
    $content = file_get_contents($pcBuilderFile);
    
    // Look for duplicate 'currentCategory' declaration
    if (preg_match('/let\s+currentCategory.*?let\s+currentCategory/s', $content)) {
        echo "<p>Found duplicate currentCategory declaration. Fixing...</p>";
        
        // Replace the second occurrence with 'let currentCat'
        $newContent = preg_replace('/let\s+currentCategory\s*=/s', 'let currentCat =', $content, 1);
        
        if ($newContent !== $content) {
            file_put_contents($pcBuilderFile, $newContent);
            echo "<p>Fixed duplicate variable declaration in pc-builder1.php</p>";
        }
    } else {
        echo "<p>No duplicate currentCategory declaration found.</p>";
    }
}

// Create default.png for monitor directory if it doesn't exist
$monitorDir = $rootDir . '/assets/images/components/monitor';
if (!file_exists($monitorDir)) {
    mkdir($monitorDir, 0777, true);
    echo "<p>Created monitor images directory.</p>";
}

$defaultImage = $monitorDir . '/default.png';
if (!file_exists($defaultImage)) {
    // Create a simple placeholder image
    $width = 300;
    $height = 300;
    $image = imagecreatetruecolor($width, $height);
    
    // Set background color (dark gray)
    $bgColor = imagecolorallocate($image, 40, 40, 40);
    imagefill($image, 0, 0, $bgColor);
    
    // Add component type text
    $textColor = imagecolorallocate($image, 200, 200, 200);
    $text = "Monitor";
    $font = 5; // Built-in font
    
    // Calculate text position to center it
    $textWidth = strlen($text) * 8; // Approximate width
    $textHeight = 14; // Approximate height
    $textX = ($width - $textWidth) / 2;
    $textY = ($height - $textHeight) / 2;
    
    // Add text to image
    imagestring($image, $font, $textX, $textY, $text, $textColor);
    
    // Save the image
    if (imagepng($image, $defaultImage)) {
        echo "<p>Created default monitor image at: " . htmlspecialchars($defaultImage) . "</p>";
    } else {
        echo "<p>Failed to create default monitor image.</p>";
    }
    
    imagedestroy($image);
} else {
    echo "<p>Default monitor image already exists.</p>";
}

echo "<h2>Fix Complete!</h2>";
echo "<p>Please refresh the PC Builder page to see the fixed icons and component selection.</p>";
?>
