<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wind Net PC - Premium Gaming & Custom Computers</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            200: '#bae6fd',
                            300: '#7dd3fc',
                            400: '#38bdf8',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                            800: '#075985',
                            900: '#0c4a6e',
                        },
                        secondary: {
                            50: '#f5f3ff',
                            500: '#8b5cf6',
                            700: '#6d28d9',
                            900: '#4c1d95',
                        },
                        accent: {
                            400: '#fb7185',
                            500: '#f43f5e',
                            600: '#e11d48',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-900 text-white font-sans">
    <!-- Gradient background overlay -->
    <div class="fixed inset-0 bg-gradient-to-br from-primary-900 via-secondary-900 to-gray-900 opacity-50 z-0"></div>
    
    <!-- Animated particles effect -->
    <div class="fixed inset-0 z-0 opacity-30">
        <svg width="100%" height="100%">
            <rect width="100%" height="100%" fill="url(#grid)" />
            <defs>
                <pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse">
                    <path d="M 40 0 L 0 0 0 40" fill="none" stroke="rgba(255,255,255,0.3)" stroke-width="0.5" />
                </pattern>
            </defs>
        </svg>
    </div>

    <div class="relative z-10">
        <!-- Header with glass morphism effect -->
        <header class="sticky top-0 backdrop-blur-md bg-black bg-opacity-40 border-b border-white border-opacity-10 py-4 px-6">
            <div class="container mx-auto flex justify-between items-center">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-primary-400 to-secondary-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-wind text-white"></i>
                    </div>
                    <h1 class="text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-primary-300 to-secondary-300">
                        Wind Net <span class="font-light">Computers</span>
                    </h1>
                </div>
                
                <nav class="hidden md:block">
                    <ul class="flex space-x-8">
                        <li><a href="#products" class="hover:text-primary-300 transition-colors duration-300 font-medium">Products</a></li>
                        <li><a href="#custom-pc" class="hover:text-primary-300 transition-colors duration-300 font-medium">Custom Builds</a></li>
                        <li><a href="#gallery" class="hover:text-primary-300 transition-colors duration-300 font-medium">Gallery</a></li>
                        <li><a href="#contact" class="hover:text-primary-300 transition-colors duration-300 font-medium">Contact</a></li>
                    </ul>
                </nav>
                
                <div class="flex items-center space-x-4">
                    <a href="#" class="p-2 rounded-full hover:bg-white hover:bg-opacity-10 transition-colors duration-300">
                        <i class="fas fa-search"></i>
                    </a>
                    <a href="#" class="p-2 rounded-full hover:bg-white hover:bg-opacity-10 transition-colors duration-300">
                        <i class="fas fa-shopping-cart"></i>
                    </a>
                    <button class="md:hidden p-2 rounded-full hover:bg-white hover:bg-opacity-10 transition-colors duration-300">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
            </div>
        </header>

        <!-- Hero section with animated gradient -->
        <section id="hero" class="relative py-24 overflow-hidden">
            <div class="absolute top-0 right-0 -mt-16 -mr-16 w-64 h-64 bg-primary-600 rounded-full filter blur-3xl opacity-20 animate-pulse"></div>
            <div class="absolute bottom-0 left-0 -mb-16 -ml-16 w-80 h-80 bg-secondary-700 rounded-full filter blur-3xl opacity-20 animate-pulse" style="animation-delay: 1s;"></div>
            
            <div class="container mx-auto px-6 relative z-10">
                <div class="max-w-3xl">
                    <h2 class="text-5xl font-bold leading-tight mb-6">
                        <span class="block">Next-Gen Computing</span>
                        <span class="bg-clip-text text-transparent bg-gradient-to-r from-primary-300 via-secondary-300 to-accent-400">
                            Engineered for Performance
                        </span>
                    </h2>
                    <p class="text-xl text-gray-300 mb-8">
                        Craft your ultimate gaming experience with custom-built PCs featuring cutting-edge components and expert assembly.
                    </p>
                    <button type="button" id="build-pc-btn" class="px-8 py-3 rounded-lg bg-gradient-to-r from-primary-600 to-primary-500 hover:from-primary-500 hover:to-primary-400 text-white font-medium transition-all duration-300 transform hover:-translate-y-1 shadow-lg shadow-primary-600/30">
                        Build Your PC
                    </button>

                        <a href="#products" class="px-8 py-3 rounded-lg bg-white bg-opacity-10 backdrop-blur-sm hover:bg-opacity-20 border border-white border-opacity-10 text-white font-medium transition-all duration-300 transform hover:-translate-y-1">
                            Explore Products
                        </a>
                    </div>
                </div>
            </div>
        </section>
    
    <!-- Tailwind CSS (CDN) -->
    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="stylesheet" href="styles.css">  
</head>
<body>

    <?php include 'includes/featured-products.html'; ?>
    <?php include 'includes/pc-builder.php'; ?>
    <?php include 'includes/customer-testimonials.html'; ?>

</body>
</html>
