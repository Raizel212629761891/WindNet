<!-- Component Selector Modal -->
    <div id="component-selector-modal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black bg-opacity-50" onclick="closeComponentSelector()"></div>
        <div class="relative bg-gray-900 rounded-2xl shadow-2xl max-w-7xl w-full mx-auto my-8 max-h-[90vh] overflow-hidden border border-blue-900/40">
            <!-- Modal Header -->
            <div class="flex items-center justify-between px-8 py-5 border-b border-blue-800/40 bg-gray-800/80">
                <div class="flex items-center gap-3">
                    <i data-lucide="cpu" class="w-7 h-7 text-blue-400"></i>
                    <h3 class="text-2xl font-bold text-white drop-shadow" id="modal-title">Select Component</h3>
                </div>
                <button onclick="closeComponentSelector()" class="text-gray-400 hover:text-blue-400 transition-colors rounded-full p-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
                    <i data-lucide="x" class="w-7 h-7"></i>
                </button>
            </div>

            <!-- Components Grid -->
            <div class="overflow-y-auto" style="max-height: calc(90vh - 8rem);">
                <div id="component-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 p-4">
                    <!-- Components will be loaded here -->
                </div>
            </div>
        </div>
    </div>