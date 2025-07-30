<x-admin-layout title="Discos">





<div class="p-4">
    @if(session('success'))
        <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50" role="alert">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
            {{ session('error') }}
        </div>
    @endif

    <div class="p-4 bg-white border border-gray-200 rounded-lg shadow-sm">

        <div class="flex flex-col gap-4 mb-4">
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-semibold text-gray-900">Todos os Discos</h1>
                <a href="{{ route('admin.vinyls.create') }}"
                   class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:ring-4 focus:ring-blue-300">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"></path>
                    </svg>
                    Adicionar novo disco
                </a>
            </div>

            <form method="GET" action="{{ route('admin.vinyls.index') }}" id="filter-form" class="flex gap-4 items-center">
                <div class="flex-1">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <svg class="w-4 h-4 text-gray-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                            </svg>
                        </div>
                        <input type="search"
                               name="search"
                               value="{{ request('search') }}"
                               class="block w-full p-2.5 pl-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-white focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Buscar por título, artista ou gravadora...">
                    </div>
                </div>
                <div class="flex-none">
                     <select name="category_id" onchange="document.getElementById('filter-form').submit()" class="p-2.5 text-sm text-gray-900 border border-gray-300 rounded-lg bg-white focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Todas as categorias</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex-none">
                     <select name="stock_status" onchange="document.getElementById('filter-form').submit()" class="p-2.5 text-sm text-gray-900 border border-gray-300 rounded-lg bg-white focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Todos os estoques</option>
                        <option value="available" {{ request('stock_status') == 'available' ? 'selected' : '' }}>
                            ✅ Disponíveis
                        </option>
                        <option value="unavailable" {{ request('stock_status') == 'unavailable' ? 'selected' : '' }}>
                            ❌ Indisponíveis
                        </option>
                        <option value="low_stock" {{ request('stock_status') == 'low_stock' ? 'selected' : '' }}>
                            ⚠️ Estoque baixo (≤5)
                        </option>
                    </select>
                </div>
                <button type="submit"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:ring-4 focus:ring-blue-300">
                    Buscar
                </button>
                @if(request('search') || request('category_id') || request('stock_status'))
                    <a href="{{ route('admin.vinyls.index') }}"
                       class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 focus:ring-4 focus:ring-gray-200">
                        Limpar
                    </a>
                @endif
            </form>
        </div>

        @if($vinyls->isEmpty())
            <div class="flex flex-col items-center justify-center py-12">
                <div class="flex items-center justify-center w-16 h-16 mb-4 rounded-full bg-gray-100">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                @if(request('search'))
                    <h3 class="mb-2 text-xl font-bold text-gray-900">Nenhum resultado encontrado</h3>
                    <p class="text-gray-500">Tente usar termos diferentes na busca</p>
                @else
                    <h3 class="mb-2 text-xl font-bold text-gray-900">Nenhum disco cadastrado</h3>
                    <p class="text-gray-500">Vamos começar?</p>
                @endif
            </div>
        @else
            @if(request('search') || request('category_id') || request('stock_status'))
                <div class="mb-4">
                    <div class="flex flex-wrap items-center gap-2 text-sm text-gray-600">
                        <span>Filtros ativos:</span>

                        @if(request('search'))
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                🔍 "{{ request('search') }}"
                            </span>
                        @endif

                        @if(request('category_id'))
                            @php
                                $selectedCategory = $categories->find(request('category_id'));
                            @endphp
                            @if($selectedCategory)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                    📂 {{ $selectedCategory->name }}
                                </span>
                            @endif
                        @endif

                        @if(request('stock_status'))
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ request('stock_status') == 'available' ? 'bg-green-100 text-green-800' : '' }}
                                {{ request('stock_status') == 'unavailable' ? 'bg-red-100 text-red-800' : '' }}
                                {{ request('stock_status') == 'low_stock' ? 'bg-yellow-100 text-yellow-800' : '' }}">
                                @if(request('stock_status') == 'available')
                                    ✅ Disponíveis
                                @elseif(request('stock_status') == 'unavailable')
                                    ❌ Indisponíveis
                                @elseif(request('stock_status') == 'low_stock')
                                    ⚠️ Estoque baixo
                                @endif
                            </span>
                        @endif

                        <span class="text-gray-500">•</span>
                        <span class="font-medium">{{ $vinyls->total() }}</span>
                        <span>{{ $vinyls->total() == 1 ? 'resultado' : 'resultados' }}</span>
                    </div>
                </div>
            @endif
            <div class="relative overflow-x-auto shadow-lg ">
                <table class="w-full text-sm text-left text-gray-500">
                    <thead class="text-xs text-zinc-100 uppercase bg-zinc-800 border border-gray-200">
                        <tr>
                            <th scope="col" class="px-6 py-3 ">Capa</th>
                            <th scope="col" class="px-6 py-3 ">Disco:</th>
                            <th scope="col" class="px-6 py-3 ">Valor venda</th>
                            <th scope="col" class="px-6 py-3 ">Valor Promo</th>
                            <th scope="col" class="px-6 py-3 ">Ano</th>
                            <th scope="col" class="px-6 py-3 ">Estoque</th>
                            <th scope="col" class="px-6 py-3">Status</th>
                            <th scope="col" class="px-6 py-3">Ações</th>
                        </tr>
                    </thead>
                    <tbody id="vinyl-table-body">
                        @foreach($vinyls as $vinyl)
                            <x-admin.vinyl-row :vinyl="$vinyl" />
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($vinyls->hasPages())
                <div class="py-4">
                    {{ $vinyls->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
</x-admin-layout>


{{-- JavaScript otimizado para carregamento de imagens do CDN --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Configurar carregamento otimizado das imagens
    const images = document.querySelectorAll('#vinyl-table-body img');
    const cdnUrl = '{{ config("filesystems.disks.media.url") }}';

    console.log('🖼️ Inicializando carregamento de', images.length, 'imagens do CDN:', cdnUrl);

    images.forEach(function(img, index) {
        const vinylId = img.closest('tr')?.querySelector('[data-modal-target]')?.getAttribute('data-modal-target')?.replace('crypto-modal-', '');
        const loadingIndicator = document.getElementById('loading-' + vinylId);

        // Mostrar indicador de carregamento
        if (loadingIndicator && !img.complete) {
            loadingIndicator.classList.remove('opacity-0');
            loadingIndicator.classList.add('opacity-100');
        }

        // Timeout para imagens que demoram muito
        const timeout = setTimeout(function() {
            if (!img.complete && img.src.includes(cdnUrl)) {
                console.warn('⚠️ Timeout na imagem:', img.src);
                img.onerror();
            }
        }, 3000); // 3 segundos timeout para CDN

        // Quando a imagem carregar com sucesso
        img.addEventListener('load', function() {
            clearTimeout(timeout);

            // Esconder indicador de carregamento
            if (loadingIndicator) {
                loadingIndicator.classList.remove('opacity-100');
                loadingIndicator.classList.add('opacity-0');
            }

            // Adicionar efeito de fade-in
            this.classList.remove('opacity-0');
            this.classList.add('opacity-100');

            console.log('✅ Imagem carregada:', this.src);
        });

        // Tratamento de erro melhorado
        img.addEventListener('error', function() {
            clearTimeout(timeout);

            // Esconder indicador de carregamento
            if (loadingIndicator) {
                loadingIndicator.classList.remove('opacity-100');
                loadingIndicator.classList.add('opacity-0');
            }

            if (!this.dataset.fallbackApplied) {
                this.dataset.fallbackApplied = 'true';

                // Log do erro para debug
                console.error('❌ Erro ao carregar imagem:', this.src);

                // Aplicar fallback SVG
                this.src = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjQiIGhlaWdodD0iNjQiIHZpZXdCb3g9IjAgMCA2NCA2NCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjY0IiBoZWlnaHQ9IjY0IiBmaWxsPSIjRjNGNEY2Ii8+CjxwYXRoIGQ9Ik0yMCAyMEg0NFY0NEgyMFYyMFoiIHN0cm9rZT0iIzlDQTNBRiIgc3Ryb2tlLXdpZHRoPSIyIiBmaWxsPSJub25lIi8+CjxwYXRoIGQ9Ik0yOCAzMkwzMiAyOEwzNiAzMkwzMiAzNkwyOCAzMloiIGZpbGw9IiM5Q0EzQUYiLz4KPC9zdmc+';
                this.alt = 'Imagem não disponível';
                this.title = 'Imagem não pôde ser carregada do CDN';
                this.classList.add('opacity-75');
            }
        });

        // Se a imagem já estiver carregada (cache)
        if (img.complete && img.naturalHeight !== 0) {
            if (loadingIndicator) {
                loadingIndicator.classList.add('opacity-0');
            }
            img.classList.add('opacity-100');
        }
    });

    // Indicador de progresso para muitas imagens
    if (images.length > 15) {
        let loadedCount = 0;
        const totalImages = images.length;

        // Criar barra de progresso
        const progressBar = document.createElement('div');
        progressBar.className = 'fixed top-4 right-4 bg-blue-600 text-white px-4 py-2 rounded-lg shadow-lg z-50 transition-all duration-300';
        progressBar.innerHTML = `
            <div class="flex items-center gap-3">
                <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <div>
                    <div class="text-sm font-medium">Carregando imagens do CDN</div>
                    <div class="text-xs opacity-90">
                        <span id="progress-count">0</span>/${totalImages}
                        (<span id="progress-percent">0</span>%)
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(progressBar);

        function updateProgress() {
            loadedCount++;
            const percent = Math.round((loadedCount / totalImages) * 100);

            const countEl = document.getElementById('progress-count');
            const percentEl = document.getElementById('progress-percent');

            if (countEl) countEl.textContent = loadedCount;
            if (percentEl) percentEl.textContent = percent;

            if (loadedCount >= totalImages) {
                setTimeout(function() {
                    progressBar.style.transform = 'translateX(100%)';
                    setTimeout(() => progressBar.remove(), 300);
                }, 500);
            }
        }

        // Monitorar carregamento
        images.forEach(function(img) {
            if (img.complete && img.naturalHeight !== 0) {
                updateProgress();
            } else {
                img.addEventListener('load', updateProgress);
                img.addEventListener('error', updateProgress);
            }
        });
    }

    // Debug: Mostrar estatísticas no console
    setTimeout(function() {
        const loadedImages = Array.from(images).filter(img => img.complete && img.naturalHeight !== 0).length;
        const errorImages = Array.from(images).filter(img => img.dataset.fallbackApplied).length;

        console.log('📊 Estatísticas de carregamento:');
        console.log('   ✅ Carregadas:', loadedImages);
        console.log('   ❌ Com erro:', errorImages);
        console.log('   📊 Total:', images.length);
        console.log('   🎯 Taxa de sucesso:', Math.round((loadedImages / images.length) * 100) + '%');
    }, 5000);
});
</script>
