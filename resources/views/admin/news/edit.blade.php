<x-admin-layout title="Editar Notícia">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900">Editar Notícia</h1>

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
                            <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Editar: {{ Str::limit($news->title, 30) }}</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <p class="text-zinc-600 mt-1">Modificar artigo: {{ $news->title }}</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('admin.news.show', $news) }}"
               class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors duration-200">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                </svg>
                Visualizar
            </a>
            <a href="{{ route('admin.news.index') }}"
               class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-colors duration-200">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Voltar
            </a>
        </div>
    </div>

    <!-- Validation Errors -->
    <x-admin.validation-errors />

    <!-- Form -->
    <form action="{{ route('admin.news.update', $news) }}" method="POST" enctype="multipart/form-data" id="newsForm">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content Column (2/3) -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Title -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <label for="title" class="block text-sm font-medium text-gray-700">
                            Título <span class="text-red-500">*</span>
                        </label>
                        <button type="button"
                                onclick="openAIModal('title')"
                                class="inline-flex items-center px-3 py-1.5 bg-purple-100 hover:bg-purple-200 text-purple-700 text-xs font-medium rounded-md transition-colors duration-200">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                            IA
                        </button>
                    </div>
                    <input type="text"
                           id="title"
                           name="title"
                           value="{{ old('title', $news->title) }}"
                           class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Digite o título da notícia..."
                           required>
                    <x-admin.form-error field="title" />
                </div>

                <!-- Content -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <label for="content" class="block text-sm font-medium text-gray-700">
                            Conteúdo <span class="text-red-500">*</span>
                        </label>
                        <button type="button"
                                onclick="openAIModal('content')"
                                class="inline-flex items-center px-3 py-1.5 bg-purple-100 hover:bg-purple-200 text-purple-700 text-xs font-medium rounded-md transition-colors duration-200">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                            IA
                        </button>
                    </div>
                    <textarea id="content"
                              name="content"
                              rows="12"
                              class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                              placeholder="Digite o conteúdo da notícia..."
                              required>{{ old('content', $news->content) }}</textarea>
                    <x-admin.form-error field="content" />
                </div>

                <!-- Excerpt -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <label for="excerpt" class="block text-sm font-medium text-gray-700">
                            Resumo
                        </label>
                        <button type="button"
                                onclick="openAIModal('excerpt')"
                                class="inline-flex items-center px-3 py-1.5 bg-purple-100 hover:bg-purple-200 text-purple-700 text-xs font-medium rounded-md transition-colors duration-200">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                            IA
                        </button>
                    </div>
                    <textarea id="excerpt"
                              name="excerpt"
                              rows="3"
                              class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                              placeholder="Breve resumo da notícia (opcional)...">{{ old('excerpt', $news->excerpt) }}</textarea>
                    <p class="mt-1 text-sm text-gray-500">Resumo que aparecerá na listagem de notícias</p>
                    <x-admin.form-error field="excerpt" />
                </div>

                <!-- SEO Meta Data -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">SEO & Meta Dados</h3>

                    <div class="space-y-4">
                        <!-- Slug -->
                        <div>
                            <label for="slug" class="block text-sm font-medium text-gray-700">
                                Slug (URL)
                            </label>
                            <input type="text"
                                   id="slug"
                                   name="slug"
                                   value="{{ old('slug', $news->slug) }}"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="sera-gerado-automaticamente">
                            <p class="mt-1 text-sm text-gray-500">Deixe em branco para gerar automaticamente</p>
                            <x-admin.form-error field="slug" />
                        </div>

                        <!-- Meta Description -->
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label for="meta_description" class="block text-sm font-medium text-gray-700">
                                    Meta Descrição
                                </label>
                                <button type="button"
                                        onclick="openAIModal('meta_description')"
                                        class="inline-flex items-center px-3 py-1.5 bg-purple-100 hover:bg-purple-200 text-purple-700 text-xs font-medium rounded-md transition-colors duration-200">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                    </svg>
                                    IA
                                </button>
                            </div>
                            <textarea id="meta_description"
                                      name="meta_description"
                                      rows="2"
                                      maxlength="160"
                                      class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                      placeholder="Descrição para motores de busca (máx. 160 caracteres)">{{ old('meta_description', $news->meta_description) }}</textarea>
                            <p class="mt-1 text-sm text-gray-500">Recomendado: 50-160 caracteres</p>
                            <x-admin.form-error field="meta_description" />
                        </div>

                        <!-- Meta Keywords -->
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label for="meta_keywords" class="block text-sm font-medium text-gray-700">
                                    Palavras-chave
                                </label>
                                <button type="button"
                                        onclick="openAIModal('meta_keywords')"
                                        class="inline-flex items-center px-3 py-1.5 bg-purple-100 hover:bg-purple-200 text-purple-700 text-xs font-medium rounded-md transition-colors duration-200">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                    </svg>
                                    IA
                                </button>
                            </div>
                            <input type="text"
                                   id="meta_keywords"
                                   name="meta_keywords"
                                   value="{{ old('meta_keywords', $news->meta_keywords) }}"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="palavra1, palavra2, palavra3">
                            <p class="mt-1 text-sm text-gray-500">Separe as palavras-chave com vírgulas</p>
                            <x-admin.form-error field="meta_keywords" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar Column (1/3) -->
            <div class="space-y-6">
                <!-- Publish Settings -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Configurações de Publicação</h3>

                    <div class="space-y-4">
                        <!-- Status -->
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700">
                                Status <span class="text-red-500">*</span>
                            </label>
                            <select id="status"
                                    name="status"
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                    required>
                                <option value="draft" {{ old('status', $news->status) === 'draft' ? 'selected' : '' }}>Rascunho</option>
                                <option value="published" {{ old('status', $news->status) === 'published' ? 'selected' : '' }}>Publicado</option>
                                <option value="archived" {{ old('status', $news->status) === 'archived' ? 'selected' : '' }}>Arquivado</option>
                            </select>
                            <x-admin.form-error field="status" />
                        </div>

                        <!-- Featured -->
                        <div class="flex items-center">
                            <input type="checkbox"
                                   id="featured"
                                   name="featured"
                                   value="1"
                                   {{ old('featured', $news->featured) ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="featured" class="ml-2 block text-sm text-gray-700">
                                Marcar como destaque
                            </label>
                        </div>

                        <!-- Published At -->
                        <div>
                            <label for="published_at" class="block text-sm font-medium text-gray-700">
                                Data de Publicação
                            </label>
                            <input type="datetime-local"
                                   id="published_at"
                                   name="published_at"
                                   value="{{ old('published_at', $news->published_at ? $news->published_at->format('Y-m-d\TH:i') : '') }}"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <p class="mt-1 text-sm text-gray-500">Deixe em branco para usar a data atual</p>
                            <x-admin.form-error field="published_at" />
                        </div>
                    </div>
                </div>

                <!-- Featured Image -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Imagem de Destaque</h3>

                    <div class="space-y-4">
                        <!-- Current Image -->
                        @if($news->featured_image_url)
                            <div id="current_featured_image">
                                <img src="{{ $news->featured_image_url }}"
                                     alt="Imagem atual"
                                     class="w-full h-32 object-cover rounded-md border border-gray-200">
                                <div class="mt-2 flex items-center">
                                    <input type="checkbox"
                                           id="remove_featured_image"
                                           name="remove_featured_image"
                                           value="1"
                                           class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded"
                                           onchange="toggleFeaturedImageRemoval(this)">
                                    <label for="remove_featured_image" class="ml-2 block text-sm text-red-600">
                                        Remover imagem atual
                                    </label>
                                </div>
                            </div>
                        @endif

                        <div class="drop-zone border-2 border-dashed border-gray-300 rounded-lg p-4 hover:border-blue-400 transition-colors" data-type="featured">
                            <input type="file"
                                   id="featured_image"
                                   name="featured_image"
                                   accept="image/*"
                                   class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            <p class="mt-1 text-sm text-gray-500">
                                @if($news->featured_image_url)
                                    Selecione uma nova imagem para substituir a atual
                                @else
                                    JPG, PNG, GIF até 5MB. Mín: 300x200px
                                @endif
                            </p>
                            <p class="text-xs text-gray-400 mt-1">Ou arraste e solte uma imagem aqui</p>
                            <x-admin.form-error field="featured_image" />
                        </div>

                        <!-- Preview -->
                        <div id="featured-image-preview" class="mt-4">
                            @if($news->featured_image_url)
                                <div class="relative">
                                    <img src="{{ $news->featured_image_url }}" alt="Current featured image" class="w-full h-48 object-cover rounded-lg">
                                    <div class="absolute top-2 left-2 bg-blue-500 text-white px-2 py-1 rounded text-xs">
                                        Imagem atual
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Gallery Images -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Galeria de Imagens</h3>

                    <div class="space-y-4">
                        <!-- Current Gallery -->
                        @if($news->gallery_image_urls && count($news->gallery_image_urls) > 0)
                            <div>
                                <h4 class="text-sm font-medium text-gray-700 mb-2">Imagens Atuais</h4>
                                <div class="grid grid-cols-2 gap-2" id="current_gallery">
                                    @foreach($news->gallery_image_urls as $index => $imageUrl)
                                        <div class="relative gallery-item" data-image="{{ $news->gallery_images[$index] }}">
                                            <img src="{{ $imageUrl }}"
                                                 alt="Gallery {{ $index + 1 }}"
                                                 class="w-full h-20 object-cover rounded-md border border-gray-200">
                                            <div class="absolute top-1 right-1 flex space-x-1">
                                                <input type="checkbox"
                                                       name="remove_gallery_images[]"
                                                       value="{{ $news->gallery_images[$index] }}"
                                                       class="w-4 h-4 text-red-600 bg-white border-2 border-red-500 rounded focus:ring-red-500"
                                                       onchange="toggleGalleryImageRemoval(this)">
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <p class="mt-2 text-xs text-gray-500">Marque as imagens que deseja remover</p>
                            </div>
                        @endif

                        <div class="drop-zone border-2 border-dashed border-gray-300 rounded-lg p-4 hover:border-blue-400 transition-colors" data-type="gallery">
                            <input type="file"
                                   id="gallery_images"
                                   name="gallery_images[]"
                                   accept="image/*"
                                   multiple
                                   class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            <p class="mt-1 text-sm text-gray-500">Adicionar novas imagens. Múltiplas imagens, máx 10. JPG, PNG, GIF até 5MB cada</p>
                            <p class="text-xs text-gray-400 mt-1">Ou arraste e solte imagens aqui</p>
                            <x-admin.form-error field="gallery_images" />
                        </div>

                        <!-- Gallery Preview -->
                        <div id="gallery-images-preview" class="grid grid-cols-2 md:grid-cols-3 gap-4 mt-4">
                            <!-- New gallery previews will be inserted here by JavaScript -->
                        </div>
                    </div>
                </div>

                <!-- Topics -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Tópicos</h3>

                    <div class="space-y-4">
                        <!-- Multi-select Topics (Enhanced by JavaScript) -->
                        <select id="topics-multiselect" name="topics[]" multiple class="hidden">
                            @foreach($topics as $topic)
                                <option value="{{ $topic->id }}"
                                        data-color="{{ $topic->color }}"
                                        data-name="{{ $topic->name }}"
                                        {{ in_array($topic->id, old('topics', $news->topics ?? [])) ? 'selected' : '' }}>
                                    {{ $topic->name }}
                                </option>
                            @endforeach
                        </select>

                        @if($topics->isEmpty())
                            <p class="text-sm text-gray-500 text-center py-4">
                                Nenhum tópico disponível.
                                <a href="{{ route('admin.news-topics.create') }}" class="text-blue-600 hover:text-blue-800">Criar primeiro tópico</a>
                            </p>
                        @endif

                        <x-admin.form-error field="topics" />
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex flex-col space-y-3">
                        <button type="submit"
                                class="w-full inline-flex justify-center items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors duration-200">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Atualizar Notícia
                        </button>

                        <a href="{{ route('admin.news.show', $news) }}"
                           class="w-full inline-flex justify-center items-center px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 font-medium rounded-lg transition-colors duration-200">
                            Cancelar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- AI Modal will be created dynamically by JavaScript -->

    <!-- Add AI Content Route Meta Tag -->
    <meta name="ai-content-route" content="{{ route('admin.news.generate-content') }}">

    @push('scripts')
    <script type="module">
        import NewsInteractiveManager from '{{ asset('js/admin/news-interactive.js') }}';
        import AIContentManager from '{{ asset('js/admin/ai-content.js') }}';

        // Initialize interactive features
        document.addEventListener('DOMContentLoaded', () => {
            window.newsManager = new NewsInteractiveManager();
            window.aiContentManager = new AIContentManager();
        });
    </script>
    @endpush
</x-admin-layout>
