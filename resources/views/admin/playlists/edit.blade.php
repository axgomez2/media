<x-admin-layout title="Editar {{ $playlist->title }}">
<div class="p-6 max-w-4xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center gap-4 mb-2">
            <a href="{{ route('admin.playlists.show', $playlist) }}"
               class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            <h1 class="text-2xl font-bold text-gray-900">
                Editar {{ $playlist->type_formatted }}
            </h1>
        </div>
    </div>

    <form action="{{ route('admin.playlists.update', $playlist) }}" method="POST" x-data="playlistForm()">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Form -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Basic Info -->
                <div class="bg-white rounded-lg shadow-sm border p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Informações Básicas</h2>

                    <div class="space-y-4">
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ $playlist->is_dj ? 'Nome do DJ' : 'Título do Chart' }}
                            </label>
                            <input type="text" id="title" name="title" value="{{ old('title', $playlist->title) }}" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @error('title')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ $playlist->is_dj ? 'Resumo' : 'Descrição' }}
                            </label>
                            <textarea id="description" name="description" rows="3"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('description', $playlist->description) }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        @if($playlist->is_chart)
                            <div>
                                <label for="chart_date" class="block text-sm font-medium text-gray-700 mb-1">
                                    Data do Chart
                                </label>
                                <input type="date" id="chart_date" name="chart_date"
                                       value="{{ old('chart_date', $playlist->chart_date?->format('Y-m-d')) }}" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                @error('chart_date')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        @endif
                    </div>
                </div>

                @if($playlist->is_dj)
                    <!-- Social Links -->
                    <div class="bg-white rounded-lg shadow-sm border p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Redes Sociais</h2>

                        <div class="space-y-4">
                            <div>
                                <label for="instagram" class="block text-sm font-medium text-gray-700 mb-1">Instagram</label>
                                <input type="url" id="instagram" name="social_links[instagram]"
                                       value="{{ old('social_links.instagram', $playlist->social_links['instagram'] ?? '') }}"
                                       placeholder="https://instagram.com/username"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            <div>
                                <label for="soundcloud" class="block text-sm font-medium text-gray-700 mb-1">SoundCloud</label>
                                <input type="url" id="soundcloud" name="social_links[soundcloud]"
                                       value="{{ old('social_links.soundcloud', $playlist->social_links['soundcloud'] ?? '') }}"
                                       placeholder="https://soundcloud.com/username"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            <div>
                                <label for="spotify" class="block text-sm font-medium text-gray-700 mb-1">Spotify</label>
                                <input type="url" id="spotify" name="social_links[spotify]"
                                       value="{{ old('social_links.spotify', $playlist->social_links['spotify'] ?? '') }}"
                                       placeholder="https://open.spotify.com/artist/..."
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Tracks Selection -->
                <div class="bg-white rounded-lg shadow-sm border p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-lg font-semibold text-gray-900">Faixas (máximo 10)</h2>
                        <span class="text-sm text-gray-500" x-text="`${selectedTracks.length}/10`"></span>
                    </div>

                    <!-- Search -->
                    <div class="mb-4">
                        <input type="text" x-model="searchTerm" placeholder="Buscar discos..."
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>

                    <!-- Selected Tracks -->
                    <div class="mb-6">
                        <h3 class="text-sm font-medium text-gray-700 mb-3">Faixas Selecionadas</h3>
                        <div class="space-y-2" x-show="selectedTracks.length > 0">
                            <template x-for="(track, index) in selectedTracks" :key="track.id">
                                <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                                    <span class="w-6 h-6 bg-blue-600 text-white rounded-full flex items-center justify-center text-xs font-medium"
                                          x-text="index + 1"></span>
                                    <span class="flex-1 text-sm font-medium" x-text="track.title"></span>
                                    <button type="button" @click="removeTrack(track.id)"
                                            class="text-red-600 hover:text-red-800">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                    <input type="hidden" :name="`tracks[${index}]`" :value="track.id">
                                </div>
                            </template>
                        </div>
                        <div x-show="selectedTracks.length === 0" class="text-sm text-gray-500 text-center py-4">
                            Nenhuma faixa selecionada
                        </div>
                    </div>

                    <!-- Available Products -->
                    <div>
                        <h3 class="text-sm font-medium text-gray-700 mb-3">Discos Disponíveis</h3>
                        <div class="max-h-96 overflow-y-auto border rounded-lg">
                            <template x-for="product in filteredProducts" :key="product.id">
                                <div class="flex items-center justify-between p-3 border-b last:border-b-0 hover:bg-gray-50">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900" x-text="product.title"></p>
                                        <p class="text-xs text-gray-500" x-text="product.productable?.title || 'Sem informações'"></p>
                                    </div>
                                    <button type="button" @click="addTrack(product)"
                                            :disabled="selectedTracks.length >= 10 || isSelected(product.id)"
                                            :class="isSelected(product.id) ? 'bg-gray-300 text-gray-500' : 'bg-blue-600 hover:bg-blue-700 text-white'"
                                            class="px-3 py-1 rounded text-xs font-medium disabled:opacity-50 disabled:cursor-not-allowed">
                                        <span x-text="isSelected(product.id) ? 'Selecionado' : 'Adicionar'"></span>
                                    </button>
                                </div>
                            </template>
                        </div>
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
                            <input type="checkbox" id="is_active" name="is_active" value="1"
                                   {{ old('is_active', $playlist->is_active) ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="is_active" class="ml-2 block text-sm text-gray-700">
                                Playlist ativa
                            </label>
                        </div>
                    </div>

                    <div class="flex gap-3 mt-6">
                        <button type="submit"
                                class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium">
                            Salvar Alterações
                        </button>
                        <a href="{{ route('admin.playlists.show', $playlist) }}"
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
function playlistForm() {
    return {
        selectedTracks: @json($playlist->tracks->map(function($track) {
            return [
                'id' => $track->product_id,
                'title' => $track->product->name ?? 'Produto não encontrado'
            ];
        })),
        searchTerm: '',
        products: @json($products),

        get filteredProducts() {
            if (!this.searchTerm) return this.products;
            return this.products.filter(product =>
                product.title.toLowerCase().includes(this.searchTerm.toLowerCase())
            );
        },

        addTrack(product) {
            if (this.selectedTracks.length < 10 && !this.isSelected(product.id)) {
                this.selectedTracks.push(product);
            }
        },

        removeTrack(productId) {
            this.selectedTracks = this.selectedTracks.filter(track => track.id !== productId);
        },

        isSelected(productId) {
            return this.selectedTracks.some(track => track.id === productId);
        }
    }
}
</script>
</x-admin-layout>
