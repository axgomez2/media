<x-admin-layout>
    <!-- Header -->
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-gray-800">📋 Histórico - Análise de Mercado</h2>
            <div class="flex space-x-3">
                <a href="{{ route('admin.market-analysis.index') }}"
                   class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg inline-flex items-center transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i> Voltar
                </a>
                <a href="{{ route('admin.market-analysis.export') }}?days={{ $days }}"
                   class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg inline-flex items-center transition-colors">
                    <i class="fas fa-download mr-2"></i> Exportar CSV
                </a>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <form method="GET" action="{{ route('admin.market-analysis.history') }}">
            <div class="flex items-end space-x-4">
                <div class="flex-1">
                    <label for="days" class="block text-sm font-medium text-gray-700 mb-2">Período (dias)</label>
                    <select name="days" id="days"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            onchange="this.form.submit()">
                        <option value="7" {{ $days == 7 ? 'selected' : '' }}>Últimos 7 dias</option>
                        <option value="15" {{ $days == 15 ? 'selected' : '' }}>Últimos 15 dias</option>
                        <option value="30" {{ $days == 30 ? 'selected' : '' }}>Últimos 30 dias</option>
                        <option value="60" {{ $days == 60 ? 'selected' : '' }}>Últimos 60 dias</option>
                        <option value="90" {{ $days == 90 ? 'selected' : '' }}>Últimos 90 dias</option>
                    </select>
                </div>
            </div>
        </form>
    </div>

    <!-- Histórico de Análises -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Análises dos Últimos {{ $days }} Dias</h3>
        </div>
        <div class="p-6">
            @if($analyses->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Listagens</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Brasil</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">EUA</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reino Unido</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Alemanha</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">França</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($analyses as $analysis)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $analysis->analysis_date->format('d/m/Y') }}</div>
                                        <div class="text-sm text-gray-500">{{ $analysis->analysis_date->format('D') }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ number_format($analysis->total_listings) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            {{ number_format($analysis->br_listings) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            {{ number_format($analysis->us_listings) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            {{ number_format($analysis->gb_listings) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            {{ number_format($analysis->de_listings) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                            {{ number_format($analysis->fr_listings) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <form action="{{ route('admin.market-analysis.destroy', $analysis->id) }}" method="POST" class="inline" onsubmit="return confirm('Tem certeza que deseja excluir este registro?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900">
                                                <i class="fas fa-trash"></i> Excluir
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="bg-blue-100 border border-blue-400 text-blue-700 px-6 py-4 rounded-lg text-center">
                    <h4 class="font-bold text-lg mb-2">Nenhuma análise encontrada</h4>
                    <p>Não há análises para o período selecionado.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Estatísticas Resumidas -->
    @if($analyses->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mt-6">
            <div class="bg-white rounded-lg shadow p-6 text-center">
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Média de Listagens</h3>
                <p class="text-3xl font-bold text-blue-600">{{ number_format($analyses->avg('total_listings')) }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-6 text-center">
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Listagens Brasil</h3>
                <p class="text-3xl font-bold text-green-600">{{ number_format($analyses->avg('br_listings')) }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-6 text-center">
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Máximo de Listagens</h3>
                <p class="text-3xl font-bold text-indigo-600">{{ number_format($analyses->max('total_listings')) }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-6 text-center">
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Listagens EUA</h3>
                <p class="text-3xl font-bold text-red-600">{{ number_format($analyses->avg('us_listings')) }}</p>
            </div>
        </div>
    @endif

    <!-- Modal para Detalhes -->
    <div x-data="{
        open: false,
        analysisId: null,
        analysisData: null,
        analyses: @js($analyses->keyBy('id'))
    }">
        <div x-show="open" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="open = false"></div>

                <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full sm:p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Detalhes da Análise</h3>
                        <button @click="open = false" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div x-show="analysisData" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h4 class="font-semibold text-gray-800 mb-3">Informações Gerais</h4>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <table class="w-full text-sm">
                                    <template x-if="analysisData">
                                        <tbody>
                                            <tr class="border-b border-gray-200 last:border-b-0">
                                                <td class="py-2 font-medium text-gray-600">Data:</td>
                                                <td class="py-2" x-text="new Date(analysisData.analysis_date).toLocaleDateString('pt-BR')"></td>
                                            </tr>
                                            <tr class="border-b border-gray-200 last:border-b-0">
                                                <td class="py-2 font-medium text-gray-600">Total Listagens:</td>
                                                <td class="py-2" x-text="analysisData.total_listings?.toLocaleString()"></td>
                                            </tr>
                                            <tr class="border-b border-gray-200 last:border-b-0">
                                                <td class="py-2 font-medium text-gray-600">Preço Médio:</td>
                                                <td class="py-2" x-text="'R$ ' + analysisData.avg_price?.toFixed(2).replace('.', ',')"></td>
                                            </tr>
                                            <tr class="border-b border-gray-200 last:border-b-0">
                                                <td class="py-2 font-medium text-gray-600">Preço Mediano:</td>
                                                <td class="py-2" x-text="'R$ ' + analysisData.median_price?.toFixed(2).replace('.', ',')"></td>
                                            </tr>
                                            <tr class="border-b border-gray-200 last:border-b-0">
                                                <td class="py-2 font-medium text-gray-600">Preço Mínimo:</td>
                                                <td class="py-2" x-text="'R$ ' + analysisData.min_price?.toFixed(2).replace('.', ',')"></td>
                                            </tr>
                                            <tr class="border-b border-gray-200 last:border-b-0">
                                                <td class="py-2 font-medium text-gray-600">Preço Máximo:</td>
                                                <td class="py-2" x-text="'R$ ' + analysisData.max_price?.toFixed(2).replace('.', ',')"></td>
                                            </tr>
                                            <tr class="border-b border-gray-200 last:border-b-0">
                                                <td class="py-2 font-medium text-gray-600">Listagens Brasil:</td>
                                                <td class="py-2" x-text="analysisData.brazil_listings?.toLocaleString()"></td>
                                            </tr>
                                            <tr class="border-b border-gray-200 last:border-b-0">
                                                <td class="py-2 font-medium text-gray-600">Preço Médio BR:</td>
                                                <td class="py-2" x-text="analysisData.brazil_avg_price ? 'R$ ' + analysisData.brazil_avg_price.toFixed(2).replace('.', ',') : '-'"></td>
                                            </tr>
                                        </tbody>
                                    </template>
                                </table>
                            </div>
                        </div>

                        <div>
                            <h4 class="font-semibold text-gray-800 mb-3">Top Releases</h4>
                            <div class="bg-gray-50 rounded-lg p-4 max-h-64 overflow-y-auto">
                                <template x-if="analysisData && analysisData.top_releases && analysisData.top_releases.length > 0">
                                    <div>
                                        <template x-for="(release, index) in analysisData.top_releases.slice(0, 5)" :key="index">
                                            <div class="mb-3 pb-3 border-b border-gray-200 last:border-b-0">
                                                <div class="font-medium text-gray-800" x-text="release.title"></div>
                                                <div class="text-sm text-gray-600" x-text="release.artist"></div>
                                                <div class="text-sm text-blue-600" x-text="release.listings + ' listagens'"></div>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                                <template x-if="!analysisData || !analysisData.top_releases || analysisData.top_releases.length === 0">
                                    <p class="text-gray-500 text-center">Nenhum release encontrado</p>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Função para mostrar detalhes -->
        <script>
            function showDetails(analysisId) {
                const modal = document.querySelector('[x-data]').__x.$data;
                modal.analysisId = analysisId;
                modal.analysisData = modal.analyses[analysisId];
                modal.open = true;
            }
        </script>
    </div>
</x-admin-layout>
