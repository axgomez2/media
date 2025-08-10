<x-admin-layout title="Criar {{ $type === 'dj' ? 'DJ Set' : 'Chart' }}">
<div class="p-6 max-w-4xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center gap-4 mb-2">
            <a href="{{ route('admin.playlists.index') }}"
               class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            <h1 class="text-2xl font-bold text-gray-900">
                Criar {{ $type === 'dj' ? 'DJ Set' : 'Chart' }}
            </h1>
        </div>
    </div>

    <form action="{{ route('admin.playlists.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="type" value="{{ $type }}">

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Form -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Basic Info -->
                <div class="bg-white rounded-lg shadow-sm border p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Informações Básicas</h2>

                    <div class="space-y-4">
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ $type === 'dj' ? 'Nome do DJ' : 'Título do Chart' }}
                            </label>
                            <input type="text" id="title" name="title" value="{{ old('title') }}" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @error('title')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ $type === 'dj' ? 'Resumo' : 'Descrição' }}
                            </label>
                            <textarea id="description" name="description" rows="3"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('description') }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        @if($type === 'chart')
                            <div>
                                <label for="chart_date" class="block text-sm font-medium text-gray-700 mb-1">
                                    Data do Chart
                                </label>
                                <input type="date" id="chart_date" name="chart_date" value="{{ old('chart_date') }}" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                @error('chart_date')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        @endif
                    </div>
                </div>

                @if($type === 'dj')
                    <!-- Social Links -->
                    <div class="bg-white rounded-lg shadow-sm border p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Redes Sociais</h2>

                        <div class="space-y-4">
                            <div>
                                <label for="instagram" class="block text-sm font-medium text-gray-700 mb-1">Instagram</label>
                                <input type="url" id="instagram" name="social_links[instagram]" value="{{ old('social_links.instagram') }}"
                                       placeholder="https://instagram.com/username"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            <div>
                                <label for="soundcloud" class="block text-sm font-medium text-gray-700 mb-1">SoundCloud</label>
                                <input type="url" id="soundcloud" name="social_links[soundcloud]" value="{{ old('social_links.soundcloud') }}"
                                       placeholder="https://soundcloud.com/username"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            <div>
                                <label for="spotify" class="block text-sm font-medium text-gray-700 mb-1">Spotify</label>
                                <input type="url" id="spotify" name="social_links[spotify]" value="{{ old('social_links.spotify') }}"
                                       placeholder="https://open.spotify.com/artist/..."
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                        </div>
                    </div>

                    <!-- DJ Photo -->
                    <div class="mt-6">
                        <label for="dj_photo" class="block text-sm font-medium text-gray-700 mb-1">Foto do DJ</label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-gray-400 transition-colors">
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="flex text-sm text-gray-600">
                                    <label for="dj_photo" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                        <span>Enviar foto</span>
                                        <input id="dj_photo" name="dj_photo" type="file" class="sr-only" accept="image/*">
                                    </label>
                                    <p class="pl-1">ou arraste e solte</p>
                                </div>
                                <p class="text-xs text-gray-500">PNG, JPG, GIF até 2MB</p>
                            </div>
                        </div>
                        @error('dj_photo')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror

                        <!-- Preview da imagem -->
                        <div id="photo-preview" class="mt-4 hidden">
                            <div class="relative inline-block">
                                <img id="preview-image" src="" alt="Preview" class="h-32 w-32 object-cover rounded-lg border">
                                <button type="button" id="remove-preview" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-600">
                                    ×
                                </button>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Tracks Selection with Search -->
                <div class="bg-white rounded-lg shadow-sm border p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Faixas (máximo 10)</h2>

                    <div class="space-y-4" id="tracks-container">
                        @for($i = 0; $i < 10; $i++)
                            <div class="track-selector" data-index="{{ $i }}">
                                <label for="track_{{ $i }}" class="block text-sm font-medium text-gray-700 mb-1">
                                    Faixa {{ $i + 1 }} {{ $i === 0 ? '(obrigatória)' : '(opcional)' }}
                                </label>

                                <!-- Custom Select with Search -->
                                <div class="relative">
                                    <div class="relative">
                                        <input type="text"
                                               id="search_{{ $i }}"
                                               class="search-input track-input w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent pr-10"
                                               placeholder="Buscar disco..."
                                               autocomplete="off"
                                               data-index="{{ $i }}">
                                        <button type="button"
                                                class="dropdown-toggle absolute inset-y-0 right-0 flex items-center px-3"
                                                data-index="{{ $i }}">
                                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        </button>
                                    </div>

                                    <!-- Hidden input for form submission -->
                                    <input type="hidden"
                                           name="tracks[{{ $i }}]"
                                           id="track_value_{{ $i }}"
                                           value="{{ old("tracks.{$i}") }}"
                                           {{ $i === 0 ? 'required' : '' }}>

                                    <!-- Dropdown -->
                                    <div class="dropdown search-dropdown absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto hidden"
                                         id="dropdown_{{ $i }}">

                                        <!-- Clear option -->
                                        <div class="clear-option px-3 py-2 hover:bg-gray-100 cursor-pointer border-b text-red-600 hidden"
                                             data-index="{{ $i }}">
                                            <span class="text-sm">✕ Limpar seleção</span>
                                        </div>

                                        <!-- Product options will be populated by JavaScript -->
                                        <div class="products-list" id="products_{{ $i }}">
                                            <!-- Products will be inserted here -->
                                        </div>

                                        <!-- No results -->
                                        <div class="no-results px-3 py-2 text-sm text-gray-500 hidden">
                                            Nenhum disco encontrado
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endfor
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Actions -->
                <div class="bg-white rounded-lg shadow-sm border p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Ações</h2>

                    <div class="space-y-4">
                        <div class="flex items-center">
                            <input type="checkbox" id="is_active" name="is_active" value="1" checked
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="is_active" class="ml-2 block text-sm text-gray-700">
                                Playlist ativa
                            </label>
                        </div>
                    </div>

                    <div class="flex gap-3 mt-6">
                        <button type="submit"
                                class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium">
                            Criar Playlist
                        </button>
                        <a href="{{ route('admin.playlists.index') }}"
                           class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                            Cancelar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const products = @json($products);
    const oldTracks = @json(old('tracks', []));

    // Initialize each track selector
    for (let i = 0; i < 10; i++) {
        initTrackSelector(i, products, oldTracks[i] || '');
    }

    function initTrackSelector(index, products, selectedValue) {
        const searchInput = document.getElementById(`search_${index}`);
        const dropdown = document.getElementById(`dropdown_${index}`);
        const hiddenInput = document.getElementById(`track_value_${index}`);
        const productsList = document.getElementById(`products_${index}`);
        const clearOption = dropdown.querySelector('.clear-option');
        const noResults = dropdown.querySelector('.no-results');
        const dropdownToggle = document.querySelector(`.dropdown-toggle[data-index="${index}"]`);

        let isOpen = false;
        let filteredProducts = [...products];

        // Initialize with selected value if exists
        if (selectedValue) {
            const selectedProduct = products.find(p => p.id == selectedValue);
            if (selectedProduct) {
                selectProduct(selectedProduct);
            }
        }

        // Render products list
        function renderProducts() {
            productsList.innerHTML = '';

            if (filteredProducts.length === 0) {
                noResults.classList.remove('hidden');
                return;
            } else {
                noResults.classList.add('hidden');
            }

            filteredProducts.forEach(product => {
                const productDiv = document.createElement('div');
                productDiv.className = `product-option px-3 py-2 cursor-pointer hover:bg-gray-100 ${hiddenInput.value == product.id ? 'bg-blue-50 text-blue-700' : ''}`;
                productDiv.innerHTML = `
                    <div class="text-sm font-medium">${product.name}</div>
                    <div class="text-xs text-gray-500">${product.productable?.title || 'Sem informações'}</div>
                `;
                productDiv.addEventListener('click', () => selectProduct(product));
                productsList.appendChild(productDiv);
            });
        }

        // Filter products based on search
        function filterProducts(searchTerm) {
            const term = searchTerm.toLowerCase();
            filteredProducts = products.filter(product =>
                product.name.toLowerCase().includes(term) ||
                (product.productable?.title && product.productable.title.toLowerCase().includes(term))
            );
            renderProducts();
        }

        // Select a product
        function selectProduct(product) {
            hiddenInput.value = product.id;
            searchInput.value = product.name;
            searchInput.placeholder = product.name;
            clearOption.classList.remove('hidden');
            closeDropdown();
            renderProducts(); // Re-render to update selected state
        }

        // Clear selection
        function clearSelection() {
            hiddenInput.value = '';
            searchInput.value = '';
            searchInput.placeholder = 'Buscar disco...';
            clearOption.classList.add('hidden');
            filteredProducts = [...products];
            renderProducts();
            closeDropdown();
        }

        // Open dropdown
        function openDropdown() {
            isOpen = true;
            dropdown.classList.remove('hidden');
            renderProducts();
        }

        // Close dropdown
        function closeDropdown() {
            isOpen = false;
            dropdown.classList.add('hidden');
        }

        // Event listeners
        searchInput.addEventListener('focus', openDropdown);
        searchInput.addEventListener('input', (e) => {
            if (!isOpen) openDropdown();
            filterProducts(e.target.value);
        });

        dropdownToggle.addEventListener('click', (e) => {
            e.preventDefault();
            if (isOpen) {
                closeDropdown();
            } else {
                openDropdown();
            }
        });

        clearOption.addEventListener('click', clearSelection);

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!dropdown.contains(e.target) && !searchInput.contains(e.target) && !dropdownToggle.contains(e.target)) {
                closeDropdown();
            }
        });

        // Initial render
        renderProducts();
    }

    // Photo preview functionality
    const photoInput = document.getElementById('dj_photo');
    const photoPreview = document.getElementById('photo-preview');
    const previewImage = document.getElementById('preview-image');
    const removePreview = document.getElementById('remove-preview');

    if (photoInput) {
        photoInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImage.src = e.target.result;
                    photoPreview.classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            }
        });

        removePreview.addEventListener('click', function() {
            photoInput.value = '';
            photoPreview.classList.add('hidden');
            previewImage.src = '';
        });
    }
});
</script>
</x-admin-layout>
