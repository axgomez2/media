@props(['release'])

<div class="max-w-6xl mx-auto" x-data="{
    selectedCoverIndex: 0,
    showMainImage(index) {
        this.selectedCoverIndex = index;
    }
}">
    <h3 class="text-xl font-semibold mb-4 text-gray-900"></h3>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Image Column -->
        <div class="md:col-span-1">
            <!-- Capa do disco selecionada -->
            <div class="mb-3">
                @if(isset($release['images']) && count($release['images']) > 0)
                    <div class="relative group">
                        <template x-for="(image, index) in {{ json_encode($release['images']) }}" :key="index">
                            <img :src="image.uri"
                                 :alt="'{{ $release['title'] }} - ' + (index + 1)"
                                 class="rounded-lg shadow-lg w-full object-cover"
                                 style="height: 300px;"
                                 x-show="selectedCoverIndex === index"
                                 x-transition:enter="transition ease-out duration-300"
                                 x-transition:enter-start="opacity-0 transform scale-95"
                                 x-transition:enter-end="opacity-100 transform scale-100">
                        </template>

                        <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/70 to-transparent p-3 rounded-b-lg opacity-0 group-hover:opacity-100 transition-opacity">
                            <p class="text-white text-sm">Imagem <span x-text="selectedCoverIndex + 1"></span> de <span x-text="{{ count($release['images']) }}"></span></p>
                        </div>
                    </div>

                    <!-- Seletor de imagens -->
                    <div class="mt-3">
                        <h5 class="text-sm font-medium text-gray-700 mb-2">Selecione a imagem para capa:</h5>
                        <div class="grid grid-cols-5 gap-2">
                            <template x-for="(image, index) in {{ json_encode($release['images']) }}" :key="index">
                                <div
                                    @click="showMainImage(index)"
                                    class="relative cursor-pointer rounded-md overflow-hidden transition-all duration-200"
                                    :class="selectedCoverIndex === index ? 'ring-2 ring-blue-500' : 'hover:opacity-80'">
                                    <img :src="image.uri150 || image.uri"
                                         class="w-full h-16 object-cover"
                                         :alt="'{{ $release['title'] }} - ' + (index + 1)">
                                    <div
                                        x-show="selectedCoverIndex === index"
                                        class="absolute inset-0 bg-blue-500/20 flex items-center justify-center">
                                        <div class="bg-blue-500 text-white rounded-full p-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                        <input type="hidden" name="selected_cover_index" x-model="selectedCoverIndex">
                    </div>
                @else
                    <div class="rounded-lg shadow-lg w-full max-w-full h-80 flex items-center justify-center bg-gray-200">
                        <svg class="w-20 h-20 text-gray-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                @endif
            </div>

            <!-- Informações do artista com imagem -->
            @if(isset($release['artists']))
                <div class="bg-gray-50 p-3 rounded-lg">
                    <h4 class="text-sm font-medium mb-2 text-gray-700">Sobre o artista</h4>
                    <div class="flex items-center mb-2">
                        <!-- Se tiver mais detalhes do artista, poderia incluir uma imagem aqui -->
                        <div class="bg-gray-200 rounded-full w-10 h-10 flex items-center justify-center mr-3">
                            <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">
                                @if(isset($release['artists']))
                                    @foreach(is_array($release['artists']) ? $release['artists'] : [] as $artist)
                                        {{ $artist['name'] }}
                                        @if(!$loop->last), @endif
                                    @endforeach
                                @else
                                    Artista Desconhecido
                                @endif
                            </p>
                            @if(isset($release['genres']))
                                <p class="text-xs text-gray-500">
                                    {{ implode(', ', $release['genres']) }}
                                </p>
                            @endif
                            @if(isset($release['artists'][0]['id']) && isset($release['artists'][0]['name']))
                                <!-- Link para o perfil do artista no Discogs com formato correto incluindo o slug -->
                                @php
                                    $artistId = $release['artists'][0]['id'];
                                    $artistName = $release['artists'][0]['name'];
                                    $artistSlug = Str::slug($artistName);
                                    $artistUrl = "https://www.discogs.com/artist/{$artistId}-{$artistSlug}";
                                @endphp
                                <a href="{{ $artistUrl }}"
                                    target="_blank"
                                    class="text-xs text-blue-600 hover:underline mt-1 inline-block">
                                    Ver perfil no Discogs
                                </a>
                            @endif
                        </div>
                    </div>

                    @if(isset($release['styles']))
                        <div class="flex flex-wrap gap-1 mt-2">
                            @foreach(is_array($release['styles']) ? $release['styles'] : [] as $style)
                                <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full">
                                    {{ $style }}
                                </span>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif
        </div>




        <!-- Details Column -->
        <div class="md:col-span-2 space-y-6">

<div id="accordion-collapse" data-accordion="collapse">
    <h2 id="accordion-collapse-heading-1">
      <button type="button" class="flex items-center justify-between w-full p-5 font-medium rtl:text-right text-gray-500 border border-b-0 border-gray-200 rounded-t-xl focus:ring-4 focus:ring-gray-200  gap-3" data-accordion-target="#accordion-collapse-body-1" aria-expanded="true" aria-controls="accordion-collapse-body-1">
        <span>Você selecionou o disco: {{ $release['title'] }}</span>
        <svg data-accordion-icon class="w-3 h-3 rotate-180 shrink-0" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
          <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5 5 1 1 5"/>
        </svg>
      </button>
    </h2>
    <div id="accordion-collapse-body-1" class="hidden" aria-labelledby="accordion-collapse-heading-1">
      <div class="p-5 border border-b-0 border-gray-200">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <h4 class="text-sm font-medium text-gray-500">Artista</h4>
                <p class="text-base text-gray-900">{{ !empty($release['artists']) ? implode(', ', array_column($release['artists'], 'name')) : 'Desconhecido' }}</p>
            </div>
            <div>
                <h4 class="text-sm font-medium text-gray-500">Título</h4>
                <p class="text-base text-gray-900">{{ $release['title'] }}</p>
            </div>
            <div>
                <h4 class="text-sm font-medium text-gray-500">Ano</h4>
                <p class="text-base text-gray-900">{{ $release['year'] ?? 'Desconhecido' }}</p>
            </div>
            <div>
                <h4 class="text-sm font-medium text-gray-500">Gênero</h4>
                <p class="text-base text-gray-900">{{ !empty($release['genres']) && is_array($release['genres']) ? implode(', ', $release['genres']) : 'Não especificado' }}</p>
            </div>
            <div>
                <h4 class="text-sm font-medium text-gray-500">Estilo</h4>
                <p class="text-base text-gray-900">{{ !empty($release['styles']) && is_array($release['styles']) ? implode(', ', $release['styles']) : 'Não especificado' }}</p>
            </div>
            <div>
                <h4 class="text-sm font-medium text-gray-500">País</h4>
                <p class="text-base text-gray-900">{{ $release['country'] ?? 'Desconhecido' }}</p>
            </div>
            @if(isset($release['labels']))
                <div>
                    <h4 class="text-sm font-medium text-gray-500">Gravadora</h4>
                    <p class="text-base text-gray-900">{{ !empty($release['labels']) ? implode(', ', array_column($release['labels'], 'name')) : 'Desconhecido' }}</p>
                </div>

                <!-- Número de Catálogo -->
                @if(!empty($release['labels'][0]['catno']))
                <div>
                    <h4 class="text-sm font-medium text-gray-500">Cat. nº</h4>
                    <p class="text-base text-gray-900 font-mono">{{ $release['labels'][0]['catno'] }}</p>
                </div>
                @endif
            @endif
        </div>
      </div>
    </div>
    <h2 id="accordion-collapse-heading-2">
      <button type="button" class="flex items-center justify-between w-full p-5 font-medium rtl:text-right text-gray-500 border border-b-0 border-gray-200 focus:ring-4 focus:ring-gray-200  gap-3" data-accordion-target="#accordion-collapse-body-2" aria-expanded="false" aria-controls="accordion-collapse-body-2">
        <span>Dados de mercado:</span>
        <svg data-accordion-icon class="w-3 h-3 rotate-180 shrink-0" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
          <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5 5 1 1 5"/>
        </svg>
      </button>
    </h2>
    <div id="accordion-collapse-body-2" class="hidden" aria-labelledby="accordion-collapse-heading-2">
      <div class="p-5 border border-b-0 border-gray-200">
        <div class="bg-gray-50 p-4 rounded-lg">
            <h4 class="text-lg font-semibold mb-3 text-gray-900">Informações de Mercado (Discogs)</h4>

            <!-- Estatísticas de Preço -->
            <div class="mb-6 p-4 border border-blue-200 bg-blue-50 rounded-lg">
                <h5 class="text-base font-semibold mb-3 text-blue-800">Preços e Vendas</h5>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <h6 class="text-xs font-medium text-gray-500">Preço Sugerido</h6>
                        <p class="text-xl font-bold text-green-600">
                            {{ isset($release['suggested_price']) && $release['suggested_price'] > 0 ? 'R$ ' . number_format($release['suggested_price'], 2, ',', '.') : 'N/A' }}
                        </p>
                    </div>
                    <div>
                        <h6 class="text-xs font-medium text-gray-500">Menor Preço (Global)</h6>
                        <p class="text-lg font-semibold text-gray-800">
                            {{ isset($release['lowest_price']) && $release['lowest_price'] > 0 ? 'R$ ' . number_format($release['lowest_price'], 2, ',', '.') : 'N/A' }}
                        </p>
                    </div>
                    <div>
                        <h6 class="text-xs font-medium text-gray-500">Preço Médio (Global)</h6>
                        <p class="text-lg font-semibold text-gray-800">
                            {{ isset($release['median_price']) && $release['median_price'] > 0 ? 'R$ ' . number_format($release['median_price'], 2, ',', '.') : 'N/A' }}
                        </p>
                    </div>
                     <div>
                        <h6 class="text-xs font-medium text-gray-500">Cópias à Venda</h6>
                        <p class="text-lg font-semibold text-gray-800">{{ $release['num_for_sale'] ?? '0' }}</p>
                    </div>
                </div>
                @if(isset($release['price_source']))
                    <p class="text-xs text-gray-500 mt-2">Fonte do preço sugerido: <span class="font-medium">{{ $release['price_source'] }}</span></p>
                @endif
            </div>

            <!-- Estatísticas do Disco -->
            <div class="grid grid-cols-1 gap-6">
                <!-- Identificadores -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="text-sm font-medium mb-3 text-gray-700">Identificadores</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Código de Barras -->
                        <div>
                            <h5 class="text-xs font-medium text-gray-500">Código de Barras</h5>
                            <p class="text-sm font-mono text-gray-900">
                                @if(!empty($release['identifiers']))
                                    @php
                                        $barcode = null;
                                        foreach($release['identifiers'] as $identifier) {
                                            if(isset($identifier['type']) && strtolower($identifier['type']) == 'barcode') {
                                                $barcode = $identifier['value'];
                                                break;
                                            }
                                        }
                                    @endphp
                                    {{ $barcode ?? 'N/A' }}
                                @else
                                    N/A
                                @endif
                            </p>
                        </div>

                        <!-- Número de Catálogo (já adicionado anteriormente) -->
                        <div>
                            <h5 class="text-xs font-medium text-gray-500">Cat. nº</h5>
                            <p class="text-sm font-mono text-gray-900">
                                {{ !empty($release['labels'][0]['catno']) ? $release['labels'][0]['catno'] : 'N/A' }}
                            </p>
                        </div>

                        <!-- Outros Identificadores -->
                        @if(!empty($release['identifiers']))
                            @foreach($release['identifiers'] as $identifier)
                                @if(isset($identifier['type']) && strtolower($identifier['type']) != 'barcode')
                                    <div>
                                        <h5 class="text-xs font-medium text-gray-500">{{ ucfirst($identifier['type'] ?? 'Identificador') }}</h5>
                                        <p class="text-sm font-mono text-gray-900">{{ $identifier['value'] ?? 'N/A' }}</p>
                                    </div>
                                @endif
                            @endforeach
                        @endif
                    </div>
                </div>

                <!-- Estatísticas de Coleção -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="text-sm font-medium mb-3 text-gray-700">Estatísticas de Coleção</h4>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                        <!-- Possuem -->
                        <div>
                            <h5 class="text-xs font-medium text-gray-500">Possuem</h5>
                            <p class="text-base font-semibold text-gray-900">
                                {{ isset($release['community']['have']) ? number_format($release['community']['have'], 0, ',', '.') : 'N/A' }}
                            </p>
                        </div>

                        <!-- Querem -->
                        <div>
                            <h5 class="text-xs font-medium text-gray-500">Querem</h5>
                            <p class="text-base font-semibold text-gray-900">
                                {{ isset($release['community']['want']) ? number_format($release['community']['want'], 0, ',', '.') : 'N/A' }}
                            </p>
                        </div>

                        <!-- Avaliação Média -->
                        <div>
                            <h5 class="text-xs font-medium text-gray-500">Avaliação Média</h5>
                            <p class="text-base font-semibold text-gray-900 flex items-center">
                                @if(isset($release['community']['rating']['average']))
                                    {{ number_format($release['community']['rating']['average'], 2, ',', '.') }}
                                    <span class="text-yellow-500 ml-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                    </span>
                                @else
                                    N/A
                                @endif
                            </p>
                        </div>

                        <!-- Avaliações -->
                        <div>
                            <h5 class="text-xs font-medium text-gray-500">Avaliações</h5>
                            <p class="text-base font-semibold text-gray-900">
                                {{ isset($release['community']['rating']['count']) ? number_format($release['community']['rating']['count'], 0, ',', '.') : 'N/A' }}
                            </p>
                        </div>

                        <!-- Quantidade à venda -->
                        <div>
                            <h5 class="text-xs font-medium text-gray-500">À venda</h5>
                            <p class="text-base font-semibold text-gray-900">
                                {{ isset($release['num_for_sale']) ? number_format($release['num_for_sale'], 0, ',', '.') : 'N/A' }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Informações de Preço -->
                @php
                    $rawMarketData = $release['raw_market_data'] ?? [];
                @endphp
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="text-sm font-medium mb-3 text-gray-700">Informações de Preço</h4>
                    <div class="grid grid-cols-3 gap-4">
                        <!-- Preço Baixo -->
                        <div>
                            <h5 class="text-xs font-medium text-gray-500">Baixo</h5>
                            <p class="text-base font-semibold text-gray-900">
                                @if(array_key_exists('lowest_price', $rawMarketData) && is_scalar($rawMarketData['lowest_price']) && is_numeric($rawMarketData['lowest_price']))
                                    R$ {{ number_format((float)$rawMarketData['lowest_price'], 2, ',', '.') }}
                                @else
                                    N/A
                                @endif
                            </p>
                        </div>

                        <!-- Preço Mediano -->
                        <div>
                            <h5 class="text-xs font-medium text-gray-500">Mediano</h5>
                            <p class="text-base font-semibold text-gray-900">
                                @if(array_key_exists('median_price', $rawMarketData) && is_scalar($rawMarketData['median_price']) && is_numeric($rawMarketData['median_price']))
                                    R$ {{ number_format((float)$rawMarketData['median_price'], 2, ',', '.') }}
                                @else
                                    N/A
                                @endif
                            </p>
                        </div>

                        <!-- Preço Alto -->
                        <div>
                            <h5 class="text-xs font-medium text-gray-500">Alto</h5>
                            <p class="text-base font-semibold text-gray-900">
                                @if(array_key_exists('highest_price', $rawMarketData) && is_scalar($rawMarketData['highest_price']) && is_numeric($rawMarketData['highest_price']))
                                    R$ {{ number_format((float)$rawMarketData['highest_price'], 2, ',', '.') }}
                                @else
                                    N/A
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Preço Sugerido -->
                <div class="bg-green-50 p-4 rounded-lg border border-green-200">
                    <h4 class="text-base font-medium mb-2 text-green-700">Preço Sugerido para Venda</h4>
                    <p class="text-2xl font-bold text-green-800">
                        @php
                            $suggestedPrice = isset($release['suggested_price']) && is_numeric($release['suggested_price']) ? (float)$release['suggested_price'] : 0;
                        @endphp
                        R$ {{ number_format($suggestedPrice, 2, ',', '.') }}
                    </p>
                    <p class="text-xs text-green-600 mt-1">
                        Este preço é calculado com base nos dados do Discogs, ajustado pela raridade do item.
                        <span class="text-xs">{{ isset($release['price_source']) ? '(Fonte: ' . $release['price_source'] . ')' : '' }}</span>
                    </p>
                </div>

                <!-- SEÇÃO DE DIAGNÓSTICO - Mostra toda a resposta da API -->
                <div class="bg-gray-100 p-4 rounded-lg border border-gray-300 mt-4">
                    <div class="flex justify-between items-center mb-2">
                        <h4 class="text-base font-medium text-gray-800">Dados Completos da API (Diagnóstico)</h4>
                        <button
                            x-data="{}"
                            x-on:click="$el.parentNode.nextElementSibling.classList.toggle('hidden')"
                            class="text-xs bg-blue-500 hover:bg-blue-600 text-white px-2 py-1 rounded">
                            Mostrar/Ocultar
                        </button>
                    </div>
                    <div class="hidden overflow-auto" style="max-height: 500px;">
                        <div class="text-sm font-medium mb-2 text-gray-700">Market Data:</div>
                        <pre class="text-xs bg-gray-200 p-2 rounded overflow-x-auto mb-3">{{ isset($release['raw_market_data']) ? json_encode($release['raw_market_data'], JSON_PRETTY_PRINT) : 'Dados de mercado não disponíveis' }}</pre>

                        <div class="text-sm font-medium mb-2 text-gray-700">Brazil Listings:</div>
                        <pre class="text-xs bg-gray-200 p-2 rounded overflow-x-auto mb-3">{{ isset($release['brazil_listings']) ? json_encode($release['brazil_listings'], JSON_PRETTY_PRINT) : 'Dados do Brasil não disponíveis' }}</pre>

                        <div class="text-sm font-medium mb-2 text-gray-700">Release Data:</div>
                        <pre class="text-xs bg-gray-200 p-2 rounded overflow-x-auto">{{ json_encode($release, JSON_PRETTY_PRINT) }}</pre>
                    </div>
                </div>

                <!-- Dados do Brasil (se disponíveis) -->
                @if(isset($release['brazil_listings']['has_brazil_sellers']) && $release['brazil_listings']['has_brazil_sellers'] === true)
                    <div class="bg-gradient-to-r from-green-50 to-yellow-50 p-4 rounded-lg border border-green-200">
                        <div class="flex justify-between items-center mb-3">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd" />
                                </svg>
                                <h4 class="text-lg font-semibold text-green-700">🇧🇷 Mercado Brasileiro</h4>
                            </div>
                            <div class="flex items-center space-x-2">
                                <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                                    {{ isset($release['brazil_listings']['count']) ? $release['brazil_listings']['count'] : 0 }} vendedores
                                </span>
                                <span class="bg-yellow-100 text-yellow-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                                    Dados locais
                                </span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                            <!-- Preço Baixo Brasil -->
                            <div class="bg-white p-3 rounded-lg border border-green-200">
                                <div class="flex items-center justify-between">
                                    <h5 class="text-sm font-medium text-green-600">💰 Menor Preço</h5>
                                    <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <p class="text-xl font-bold text-green-800">
                                    @php
                                        $brLowestPrice = null;
                                        if (!empty($release['brazil_listings']['lowest_price']) && is_numeric($release['brazil_listings']['lowest_price'])) {
                                            $brLowestPrice = (float)$release['brazil_listings']['lowest_price'];
                                        }
                                    @endphp
                                    {{ $brLowestPrice ? 'R$ ' . number_format($brLowestPrice, 2, ',', '.') : 'N/A' }}
                                </p>
                                <p class="text-xs text-green-600 mt-1">Melhor oportunidade</p>
                            </div>

                            <!-- Preço Médio Brasil -->
                            <div class="bg-white p-3 rounded-lg border border-yellow-200">
                                <div class="flex items-center justify-between">
                                    <h5 class="text-sm font-medium text-yellow-600">📊 Preço Médio</h5>
                                    <svg class="w-4 h-4 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <p class="text-xl font-bold text-yellow-800">
                                    @php
                                        $brMedianPrice = null;
                                        if (!empty($release['brazil_listings']['median_price']) && is_numeric($release['brazil_listings']['median_price'])) {
                                            $brMedianPrice = (float)$release['brazil_listings']['median_price'];
                                        }
                                    @endphp
                                    {{ $brMedianPrice ? 'R$ ' . number_format($brMedianPrice, 2, ',', '.') : 'N/A' }}
                                </p>
                                <p class="text-xs text-yellow-600 mt-1">Referência de mercado</p>
                            </div>

                            <!-- Preço Alto Brasil -->
                            <div class="bg-white p-3 rounded-lg border border-red-200">
                                <div class="flex items-center justify-between">
                                    <h5 class="text-sm font-medium text-red-600">🔥 Maior Preço</h5>
                                    <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <p class="text-xl font-bold text-red-800">
                                    @php
                                        $brHighestPrice = null;
                                        if (!empty($release['brazil_listings']['highest_price']) && is_numeric($release['brazil_listings']['highest_price'])) {
                                            $brHighestPrice = (float)$release['brazil_listings']['highest_price'];
                                        }
                                    @endphp
                                    {{ $brHighestPrice ? 'R$ ' . number_format($brHighestPrice, 2, ',', '.') : 'N/A' }}
                                </p>
                                <p class="text-xs text-red-600 mt-1">Teto do mercado</p>
                            </div>
                        </div>

                        <!-- Análise de competitividade -->
                        @php
                            $suggestedPrice = isset($release['suggested_price']) && is_numeric($release['suggested_price']) ? (float)$release['suggested_price'] : 0;
                            $competitiveAnalysis = '';
                            $competitiveColor = 'gray';

                            if ($brLowestPrice && $suggestedPrice > 0) {
                                $difference = (($suggestedPrice - $brLowestPrice) / $brLowestPrice) * 100;
                                if ($difference < -10) {
                                    $competitiveAnalysis = 'Preço muito competitivo! ' . abs(round($difference)) . '% abaixo do menor preço brasileiro.';
                                    $competitiveColor = 'green';
                                } elseif ($difference < 10) {
                                    $competitiveAnalysis = 'Preço competitivo, próximo ao menor preço brasileiro.';
                                    $competitiveColor = 'yellow';
                                } else {
                                    $competitiveAnalysis = 'Preço ' . round($difference) . '% acima do menor preço brasileiro.';
                                    $competitiveColor = 'red';
                                }
                            }
                        @endphp

                        @if($competitiveAnalysis)
                            <div class="bg-{{ $competitiveColor }}-50 border border-{{ $competitiveColor }}-200 rounded-lg p-3">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-{{ $competitiveColor }}-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                    </svg>
                                    <div>
                                        <h6 class="text-sm font-medium text-{{ $competitiveColor }}-800">Análise Competitiva</h6>
                                        <p class="text-sm text-{{ $competitiveColor }}-700">{{ $competitiveAnalysis }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Dica de estratégia -->
                        <div class="mt-3 bg-blue-50 border border-blue-200 rounded-lg p-3">
                            <div class="flex items-start">
                                <svg class="w-5 h-5 text-blue-600 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                </svg>
                                <div>
                                    <h6 class="text-sm font-medium text-blue-800">💡 Dica Estratégica</h6>
                                    <p class="text-sm text-blue-700">
                                        Com {{ isset($release['brazil_listings']['count']) ? $release['brazil_listings']['count'] : 0 }} vendedores brasileiros, há boa disponibilidade local.
                                        Considere fatores como condição do item, reputação do vendedor e custos de envio ao precificar.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <!-- Quando não há dados do Brasil -->
                    <div class="bg-orange-50 p-4 rounded-lg border border-orange-200">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-orange-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            <div>
                                <h4 class="text-base font-medium text-orange-700">🇧🇷 Sem Dados do Mercado Brasileiro</h4>
                                <p class="text-sm text-orange-600 mt-1">
                                    Este disco não possui vendedores ativos no Brasil no momento.
                                    Isso pode representar uma oportunidade de mercado com menos concorrência local.
                                </p>
                            </div>
                        </div>

                        <!-- Sugestão quando não há dados brasileiros -->
                        <div class="mt-3 bg-white p-3 rounded border border-orange-200">
                            <h6 class="text-sm font-medium text-orange-800">💰 Estratégia Recomendada</h6>
                            <ul class="text-sm text-orange-700 mt-1 list-disc list-inside space-y-1">
                                <li>Use os preços globais como referência base</li>
                                <li>Considere adicionar margem por exclusividade local</li>
                                <li>Monitore a demanda através de wantlists brasileiras</li>
                                <li>Avalie custos de importação se necessário</li>
                            </ul>
                        </div>
                    </div>
                @endif
            </div>
        </div>
      </div>
    </div>
    <h2 id="accordion-collapse-heading-3">
      <button type="button" class="flex items-center justify-between w-full p-5 font-medium rtl:text-right text-gray-500 border border-gray-200 focus:ring-4 focus:ring-gray-200  gap-3" data-accordion-target="#accordion-collapse-body-3" aria-expanded="false" aria-controls="accordion-collapse-body-3">
        <span>Faixas do disco:</span>
        <svg data-accordion-icon class="w-3 h-3 rotate-180 shrink-0" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
          <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5 5 1 1 5"/>
        </svg>
      </button>
    </h2>
    <div id="accordion-collapse-body-3" class="hidden" aria-labelledby="accordion-collapse-heading-3">
      <div class="p-5 border border-t-0 border-gray-200">
        @if(isset($release['tracklist']))
        <div x-data="{ tracks: {{ json_encode($release['tracklist']) }}, isLoading: false }"
            <h4 class="text-lg font-semibold mb-3 text-gray-900">Faixas do Disco</h4>
            <div class="space-y-4">
                <template x-for="(track, index) in tracks" :key="index">
                    <div class="grid grid-cols-12 gap-4 items-center p-3 bg-gray-50 rounded-lg border">
                        <!-- Posição da faixa -->
                        <div class="col-span-1">
                            <span class="w-8 h-8 flex items-center justify-center bg-blue-100 text-blue-800 rounded-full text-sm font-medium" x-text="track.position || (index + 1)"></span>
                        </div>

                        <!-- Nome da faixa (editável) -->
                        <div class="col-span-12 sm:col-span-4">
                            <label :for="'track_name_'+index" class="sr-only">Nome da Faixa</label>
                            <input type="text"
                                   x-model="track.title"
                                   :name="'tracks['+index+'][name]'"
                                   :id="'track_name_'+index"
                                   class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                   placeholder="Nome da Faixa"
                                   required>
                        </div>

                        <!-- Duração -->
                        <div class="col-span-12 sm:col-span-2">
                            <label :for="'track_duration_'+index" class="sr-only">Duração</label>
                            <input type="text"
                                   x-model="track.duration"
                                   :name="'tracks['+index+'][duration]'"
                                   :id="'track_duration_'+index"
                                   class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                   placeholder="Duração">
                        </div>

                        <!-- URL do YouTube -->
                        <div class="col-span-12 sm:col-span-4">
                            <label :for="'track_youtube_'+index" class="sr-only">URL YouTube</label>
                            <div class="flex">
                                <input type="url"
                                       x-model="track.youtube_url"
                                       :name="'tracks['+index+'][youtube_url]'"
                                       :id="'track_youtube_'+index"
                                       class="rounded-none rounded-s-lg bg-white border border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500 block flex-1 min-w-0 w-full text-sm p-2.5"
                                       placeholder="URL do YouTube">
                                <button type="button"
                                        @click="$dispatch('search-youtube', { trackName: track.title, artistName: '{{ !empty($release['artists']) ? implode(' ', array_column($release['artists'], 'name')) : '' }}', trackIndex: index })"
                                        :disabled="isLoading"
                                        class="inline-flex items-center px-3 text-sm text-white bg-red-600 border border-s-0 border-red-600 rounded-e-md hover:bg-red-700 disabled:opacity-50">
                                    <template x-if="!isLoading">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                                        </svg>
                                    </template>
                                    <template x-if="isLoading">
                                        <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                        </svg>
                                    </template>
                                    <span class="sr-only">Pesquisar no YouTube</span>
                                </button>
                            </div>
                        </div>

                        <!-- Botão de remover faixa -->
                        <div class="col-span-12 sm:col-span-1 flex justify-end">
                            <button type="button"
                                    @click="tracks.splice(index, 1)"
                                    class="p-2.5 text-sm font-medium text-white bg-red-600 rounded-lg border border-red-600 hover:bg-red-700 focus:ring-4 focus:outline-none focus:ring-red-300">
                                <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 18 20">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 5h16M7 8v8m4-8v8M7 1h4a1 1 0 0 1 1 1v3H6V2a1 1 0 0 1 1-1ZM3 5h12v13a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V5Z"/>
                                </svg>
                                <span class="sr-only">Excluir Faixa</span>
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Botão para adicionar nova faixa -->
            <button type="button"
                    class="mt-4 text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center inline-flex items-center"
                    @click="tracks.push({ title: '', duration: '', youtube_url: '', position: tracks.length + 1 })">
                <svg class="w-4 h-4 me-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 5.75V14.25M5.75 10H14.25"/>
                </svg>
                Adicionar Faixa
            </button>

            <!-- Informações sobre as melhorias -->
            <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800">Funcionalidades Melhoradas</h3>
                        <div class="mt-2 text-sm text-blue-700">
                            <ul class="list-disc pl-5 space-y-1">
                                <li>Edite os nomes das faixas diretamente</li>
                                <li>Busque vídeos no YouTube para cada faixa</li>
                                <li>Adicione ou remova faixas conforme necessário</li>
                                <li>As informações serão salvas quando você completar o cadastro</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @else
            <div class="text-center py-8" x-data="{ tracks: [], isLoading: false }">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhuma faixa encontrada</h3>
                <p class="mt-1 text-sm text-gray-500">Este disco não possui informações de faixas no Discogs.</p>
                <div class="mt-6">
                    <button type="button"
                            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            @click="tracks = [{ title: '', duration: '', youtube_url: '', position: 1 }]">
                        <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                        Adicionar Primeira Faixa
                    </button>
                </div>
            </div>
        @endif
      </div>
    </div>
  </div>



            <!-- Market Info -->


            <!-- Tracklist -->


            <!-- Notes - Editável -->
            <div x-data="{ notes: '{{ addslashes($release['notes'] ?? '') }}' }">
                <h4 class="text-lg font-semibold mb-2 text-gray-900">Notas e Descrição</h4>
                <div class="mb-4">
                    <textarea x-model="notes"
                              name="notes"
                              rows="4"
                              class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500"
                              placeholder="Adicione notas ou descrição sobre este disco...">{{ $release['notes'] ?? '' }}</textarea>
                    <p class="mt-1 text-xs text-gray-500">Você pode editar as notas originais do Discogs ou adicionar informações adicionais.</p>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex flex-wrap gap-2">
                <a href="{{ $release['uri'] }}"
                    target="_blank"
                    class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 focus:outline-none inline-flex items-center">
                    Link do disco no Discogs
                </a>

                <!-- Botão de salvar disco usando formulário tradicional -->
                <form action="{{ route('admin.vinyls.store') }}" method="POST" class="inline-block" id="save-vinyl-form">
                    @csrf
                    <input type="hidden" name="release_id" value="{{ $release['id'] }}">
                    <input type="hidden" name="selected_cover_index" x-bind:value="selectedCoverIndex">

                    <!-- Campo hidden para notas editadas -->
                    <input type="hidden" name="notes" x-bind:value="document.querySelector('textarea[name=notes]')?.value || ''">

                    <!-- Campos hidden para tracks editadas (serão populados via JavaScript) -->
                    <div id="tracks-hidden-fields"></div>

                    <div class="flex flex-col space-y-2 sm:flex-row sm:space-y-0 sm:space-x-2">
                        <div class="py-2">
                            <span class="text-sm text-gray-600">Imagem de capa selecionada: <span class="font-medium" x-text="selectedCoverIndex + 1"></span></span>
                        </div>

                        <button type="submit"
                                class="text-white bg-green-700 hover:bg-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 focus:outline-none inline-flex items-center"
                                @click="prepareTracksData($event)">
                            <span>Salvar disco</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Função para preparar os dados das tracks antes do envio do formulário
function prepareTracksData(event) {
    // Encontrar o container dos campos hidden
    const hiddenFieldsContainer = document.getElementById('tracks-hidden-fields');
    if (!hiddenFieldsContainer) return;

    // Limpar campos existentes
    hiddenFieldsContainer.innerHTML = '';

    // Coletar dados de todas as tracks editáveis
    const trackInputs = document.querySelectorAll('input[name*="tracks["][name*="][name]"]');

    trackInputs.forEach((nameInput, index) => {
        const trackIndex = nameInput.name.match(/tracks\[(\d+)\]/)?.[1];
        if (trackIndex === undefined) return;

        // Encontrar os campos relacionados a esta track
        const durationInput = document.querySelector(`input[name="tracks[${trackIndex}][duration]"]`);
        const youtubeInput = document.querySelector(`input[name="tracks[${trackIndex}][youtube_url]"]`);

        // Criar campos hidden para esta track
        if (nameInput.value.trim()) {
            // Nome da track
            const nameHidden = document.createElement('input');
            nameHidden.type = 'hidden';
            nameHidden.name = `tracks[${trackIndex}][name]`;
            nameHidden.value = nameInput.value;
            hiddenFieldsContainer.appendChild(nameHidden);

            // Duração da track
            if (durationInput && durationInput.value.trim()) {
                const durationHidden = document.createElement('input');
                durationHidden.type = 'hidden';
                durationHidden.name = `tracks[${trackIndex}][duration]`;
                durationHidden.value = durationInput.value;
                hiddenFieldsContainer.appendChild(durationHidden);
            }

            // URL do YouTube
            if (youtubeInput && youtubeInput.value.trim()) {
                const youtubeHidden = document.createElement('input');
                youtubeHidden.type = 'hidden';
                youtubeHidden.name = `tracks[${trackIndex}][youtube_url]`;
                youtubeHidden.value = youtubeInput.value;
                hiddenFieldsContainer.appendChild(youtubeHidden);
            }
        }
    });
}
</script>
