<x-admin-layout title="Visualizar Notícia">
    <div class="max-w-7xl mx-auto">
        <!-- Header with Title and Actions -->
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">{{ $news->title }}</h1>

                    <!-- Breadcrumbs -->
                    <nav class="flex mt-2" aria-label="Breadcrumb">
                        <ol class="inline-flex items-center space-x-1 md:space-x-3">
                            <li class="inline-flex items-center">
                                <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600">
                                    <svg class="w-3 h-3 mr-2.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="m19.707 9.293-2-2-7-7a1 1 0 0 0-1.414 0l-7 7-2 2a1 1 0 0 0 1.414 1.414L2 10.414V18a2 2 0 0 0 2 2h3a1 1 0 0 0 1-1v-4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v4a1 1 0 0 0 1 1h3a2 2 0 0 0 2-2v-7.586l.293.293a1 1 0 0 0 1.414-1.414Z"/>
                                    </svg>
                                    Dashboard
                                </a>
                            </li>
                            <li>
                                <div class="flex items-center">
                                    <svg class="w-3 h-3 text-gray-400 mx-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
                                    </svg>
                                    <a href="{{ route('admin.news.index') }}" class="ml-1 text-sm font-medium text-gray-700 hover:text-blue-600 md:ml-2">Notícias</a>
                                </div>
                            </li>
                            <li aria-current="page">
                                <div class="flex items-center">
                                    <svg class="w-3 h-3 text-gray-400 mx-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
                                    </svg>
                                    <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">{{ \Illuminate\Support\Str::limit($news->title, 30) }}</span>
                                </div>
                            </li>
                        </ol>
                    </nav>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center space-x-3">
                    <a href="{{ route('admin.news.edit', $news) }}"
                       class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Editar
                    </a>

                    <button type="button"
                            onclick="confirmDelete()"
                            class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Excluir
                    </button>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2">
                <!-- Article Metadata -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                    <div class="flex flex-wrap items-center gap-4 text-sm text-gray-600">
                        <!-- Author -->
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            <span class="font-medium">{{ $news->author->name }}</span>
                        </div>

                        <!-- Publication Date -->
                        @if($news->published_at)
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span>{{ $news->published_at->format('d/m/Y H:i') }}</span>
                        </div>
                        @endif

                        <!-- Status -->
                        <div class="flex items-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($news->status === 'published') bg-green-100 text-green-800
                                @elseif($news->status === 'draft') bg-yellow-100 text-yellow-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ $news->status_formatted }}
                            </span>
                        </div>

                        <!-- Featured Badge -->
                        @if($news->featured)
                        <div class="flex items-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                                Destaque
                            </span>
                        </div>
                        @endif

                        <!-- Reading Time -->
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>{{ $news->reading_time }} min de leitura</span>
                        </div>
                    </div>

                    <!-- Topics -->
                    @if($news->topics && count($news->topics) > 0)
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <div class="flex items-center flex-wrap gap-2">
                            <span class="text-sm font-medium text-gray-700">Tópicos:</span>
                            @php
                                $topicModels = \App\Models\NewsTopic::whereIn('id', $news->topics)->get();
                            @endphp
                            @foreach($topicModels as $topic)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium text-white"
                                  style="background-color: {{ $topic->color ?? '#6B7280' }}">
                                {{ $topic->name }}
                            </span>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Featured Image -->
                @if($news->featured_image_url)
                <div class="mb-6">
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                        <img src="{{ $news->featured_image_url }}"
                             alt="{{ $news->title }}"
                             class="w-full h-64 md:h-80 object-cover">
                    </div>
                </div>
                @endif

                <!-- Article Content -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                    <!-- Excerpt -->
                    @if($news->excerpt)
                    <div class="mb-6 p-4 bg-gray-50 rounded-lg border-l-4 border-blue-500">
                        <p class="text-lg text-gray-700 font-medium leading-relaxed">{{ $news->excerpt }}</p>
                    </div>
                    @endif

                    <!-- Content -->
                    <div class="prose prose-lg max-w-none">
                        {!! nl2br(e($news->content)) !!}
                    </div>
                </div>

                <!-- Image Gallery -->
                @if($news->gallery_image_urls && count($news->gallery_image_urls) > 0)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Galeria de Imagens</h3>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        @foreach($news->gallery_image_urls as $imageUrl)
                        <div class="relative group cursor-pointer" onclick="openImageModal('{{ $imageUrl }}')">
                            <img src="{{ $imageUrl }}"
                                 alt="Imagem da galeria"
                                 class="w-full h-32 object-cover rounded-lg transition-transform group-hover:scale-105">
                            <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-opacity rounded-lg flex items-center justify-center">
                                <svg class="w-8 h-8 text-white opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/>
                                </svg>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- SEO Information -->
                @if($news->meta_description || $news->meta_keywords)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Informações SEO</h3>

                    @if($news->meta_description)
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Meta Descrição</label>
                        <p class="text-sm text-gray-600 bg-gray-50 p-3 rounded-md">{{ $news->meta_description }}</p>
                    </div>
                    @endif

                    @if($news->meta_keywords)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Palavras-chave</label>
                        <p class="text-sm text-gray-600 bg-gray-50 p-3 rounded-md">{{ $news->meta_keywords }}</p>
                    </div>
                    @endif
                </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <!-- Article Info Card -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Informações do Artigo</h3>

                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Criado em:</span>
                            <span class="font-medium">{{ $news->created_at->format('d/m/Y') }}</span>
                        </div>

                        <div class="flex justify-between">
                            <span class="text-gray-600">Atualizado em:</span>
                            <span class="font-medium">{{ $news->updated_at->format('d/m/Y') }}</span>
                        </div>

                        <div class="flex justify-between">
                            <span class="text-gray-600">Slug:</span>
                            <span class="font-medium text-blue-600">{{ $news->slug }}</span>
                        </div>

                        @if($news->views_count)
                        <div class="flex justify-between">
                            <span class="text-gray-600">Visualizações:</span>
                            <span class="font-medium">{{ number_format($news->views_count) }}</span>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Related News -->
                @if($relatedNews->count() > 0)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Notícias Relacionadas</h3>

                    <div class="space-y-4">
                        @foreach($relatedNews as $related)
                        <div class="border-b border-gray-200 pb-4 last:border-b-0 last:pb-0">
                            <a href="{{ route('admin.news.show', $related) }}" class="group">
                                <div class="flex space-x-3">
                                    @if($related->featured_image_url)
                                    <div class="flex-shrink-0">
                                        <img src="{{ $related->featured_image_url }}"
                                             alt="{{ $related->title }}"
                                             class="w-16 h-16 object-cover rounded-md group-hover:opacity-75 transition-opacity">
                                    </div>
                                    @endif

                                    <div class="flex-1 min-w-0">
                                        <h4 class="text-sm font-medium text-gray-900 group-hover:text-blue-600 transition-colors line-clamp-2">
                                            {{ $related->title }}
                                        </h4>

                                        @if($related->excerpt)
                                        <p class="text-xs text-gray-600 mt-1 line-clamp-2">{{ $related->excerpt }}</p>
                                        @endif

                                        <div class="flex items-center mt-2 text-xs text-gray-500">
                                            @if($related->author)
                                            <span>{{ $related->author->name }}</span>
                                            <span class="mx-1">•</span>
                                            @endif
                                            <span>{{ $related->published_at ? $related->published_at->format('d/m/Y') : $related->created_at->format('d/m/Y') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Image Modal -->
    <div id="imageModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-75">
        <div class="relative max-w-4xl max-h-full p-4">
            <button onclick="closeImageModal()"
                    class="absolute top-4 right-4 text-white hover:text-gray-300 z-10">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
            <img id="modalImage" src="" alt="Imagem ampliada" class="max-w-full max-h-full object-contain">
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white rounded-lg p-6 max-w-md mx-4">
            <div class="flex items-center mb-4">
                <svg class="w-6 h-6 text-red-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                </svg>
                <h3 class="text-lg font-semibold text-gray-900">Confirmar Exclusão</h3>
            </div>

            <p class="text-gray-600 mb-6">
                Tem certeza que deseja excluir esta notícia? Esta ação não pode ser desfeita.
            </p>

            <div class="flex justify-end space-x-3">
                <button onclick="closeDeleteModal()"
                        class="px-4 py-2 text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 transition-colors">
                    Cancelar
                </button>

                <form id="deleteForm" method="POST" action="{{ route('admin.news.destroy', $news) }}" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors">
                        Excluir
                    </button>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Image modal functions
        function openImageModal(imageUrl) {
            document.getElementById('modalImage').src = imageUrl;
            document.getElementById('imageModal').classList.remove('hidden');
            document.getElementById('imageModal').classList.add('flex');
        }

        function closeImageModal() {
            document.getElementById('imageModal').classList.add('hidden');
            document.getElementById('imageModal').classList.remove('flex');
        }

        // Delete confirmation functions
        function confirmDelete() {
            document.getElementById('deleteModal').classList.remove('hidden');
            document.getElementById('deleteModal').classList.add('flex');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
            document.getElementById('deleteModal').classList.remove('flex');
        }

        // Close modals on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeImageModal();
                closeDeleteModal();
            }
        });

        // Close modals on background click
        document.getElementById('imageModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeImageModal();
            }
        });

        document.getElementById('deleteModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeDeleteModal();
            }
        });
    </script>
    @endpush
</x-admin-layout>
