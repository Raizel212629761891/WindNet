
<!-- Custom PC Builder section -->
<section id="custom-pc" class="py-20 relative bg-gray-900">
    <div class="absolute inset-0 bg-primary-900/10 z-0"></div>
    <div class="container mx-auto px-6 relative z-10">
        <?php if ($connection_error): ?>
        <div class="bg-red-500/20 border border-red-500 text-white p-4 rounded-lg mb-6">
            <p><strong>Error:</strong> Could not connect to the database. Please try again later or contact support.</p>
        </div>
        <?php endif; ?>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div>
                <h2 class="text-3xl font-bold mb-6">
                    Custom PC Builder
                    <span class="block mt-2 text-primary-300">Your Vision, Our Expertise</span>
                </h2>
                <p class="text-gray-300 mb-6">
                    Create your dream setup with our intuitive PC builder. Select components that match your performance needs and aesthetic preferences.
                </p>
                <ul class="space-y-4 mb-8">
                    <li class="flex items-start space-x-3">
                        <span class="text-primary-300 mt-1"><i class="fas fa-check-circle"></i></span>
                        <span class="text-gray-300">Expert component compatibility verification</span>
                    </li>
                    <li class="flex items-start space-x-3">
                        <span class="text-primary-300 mt-1"><i class="fas fa-check-circle"></i></span>
                        <span class="text-gray-300">Real-time performance benchmarks and estimates</span>
                    </li>
                    <li class="flex items-start space-x-3">
                        <span class="text-primary-300 mt-1"><i class="fas fa-check-circle"></i></span>
                        <span class="text-gray-300">Professional assembly with cable management</span>
                    </li>
                    <li class="flex items-start space-x-3">
                        <span class="text-primary-300 mt-1"><i class="fas fa-check-circle"></i></span>
                        <span class="text-gray-300">3-year warranty with technical support</span>
                    </li>
                </ul>
                <a href="builder/pc-builder1.php" class="inline-flex items-center px-6 py-3 rounded-lg bg-gradient-to-r from-primary-600 to-secondary-600 hover:from-primary-500 hover:to-secondary-500 text-white font-medium transition-all duration-300 transform hover:-translate-y-1 shadow-lg shadow-primary-600/30">
                    <span>Start Building</span>
                    <i class="fas fa-tools ml-2"></i>
                </a>
            </div>
            <div class="bg-gray-800 bg-opacity-50 backdrop-blur-md rounded-2xl p-6 border border-gray-700">
                <form action="process_order.php" method="POST">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2">
                            <label class="block text-gray-400 mb-2 text-sm">Select Case</label>
                            <select name="pc_case" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white focus:border-primary-400 focus:ring-2 focus:ring-primary-400 focus:ring-opacity-30 focus:outline-none transition-all duration-300">
                                <?php if (!$connection_error) echo getComponents('Case', $conn); else echo "<option>Database connection error</option>"; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-400 mb-2 text-sm">Processor</label>
                            <select name="processor" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white focus:border-primary-400 focus:ring-2 focus:ring-primary-400 focus:ring-opacity-30 focus:outline-none transition-all duration-300">
                                <?php if (!$connection_error) echo getComponents('Processor', $conn); else echo "<option>Database connection error</option>"; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-400 mb-2 text-sm">Graphics Card</label>
                            <select name="graphics_card" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white focus:border-primary-400 focus:ring-2 focus:ring-primary-400 focus:ring-opacity-30 focus:outline-none transition-all duration-300">
                                <?php if (!$connection_error) echo getComponents('Graphics Card', $conn); else echo "<option>Database connection error</option>"; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-400 mb-2 text-sm">Memory</label>
                            <select name="memory" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white focus:border-primary-400 focus:ring-2 focus:ring-primary-400 focus:ring-opacity-30 focus:outline-none transition-all duration-300">
                                <?php if (!$connection_error) echo getComponents('Memory', $conn); else echo "<option>Database connection error</option>"; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-400 mb-2 text-sm">Storage</label>
                            <select name="storage" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white focus:border-primary-400 focus:ring-2 focus:ring-primary-400 focus:ring-opacity-30 focus:outline-none transition-all duration-300">
                                <?php if (!$connection_error) echo getComponents('Storage', $conn); else echo "<option>Database connection error</option>"; ?>
                            </select>
                        </div>
                    </div>
                    <div id="chart-container" style="display: none;" class="mt-4 p-4 bg-gray-800 border border-gray-700 rounded-lg text-white">
                    <h3 class="text-lg font-semibold">Performance Score:</h3>
                    <canvas id="pcPerformanceChart"></canvas>
                    </div>
                    <button type="submit" class="mt-6 bg-primary-500 hover:bg-primary-600 text-white px-6 py-3 rounded-lg w-full">
                        Build My PC
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>