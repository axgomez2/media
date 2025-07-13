<x-admin-layout>
    <div class="space-y-8">
        <div>
            <h1 class="text-2xl font-semibold text-gray-800 ">Análise de Mercado Discogs</h1>
            <p class="text-sm text-gray-600 ">Insira os dados diários e acompanhe a evolução do mercado.</p>
        </div>

        <!-- Seção de Formulário -->
        <div class="bg-white shadow-lg rounded-lg p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold text-gray-900">Adicionar Novo Registro</h2>
                <button id="autoCollectBtn" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <i class="fas fa-robot mr-2"></i> Coletar Automaticamente
                </button>
            </div>
            <div id="autoCollectStatus" class="mb-4 hidden"></div>
            <form action="{{ route('admin.market-analysis.store') }}" method="POST" class="space-y-6">
                @csrf
                @include('admin.market-analysis._form', ['analysis' => $analysis])
                <div class="flex justify-end">
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                        <i class="fas fa-save mr-2"></i> Salvar Análise
                    </button>
                </div>
            </form>
        </div>

        <!-- Seção da Tabela -->
        <div class="bg-white  shadow-lg rounded-lg overflow-hidden">
            <div class="p-6">
                <h2 class="text-xl font-semibold text-gray-900 ">Histórico de Análises</h2>
                 @if(session('success'))
                    <div class="mt-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4" role="alert">
                        <p>{{ session('success') }}</p>
                    </div>
                @endif
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 ">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Mundo</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Brasil</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">EUA</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reino Unido</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Alemanha</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">França</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Itália</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Japão</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Canadá</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bélgica</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Suécia</th>
                            <th scope="col" class="relative px-6 py-3"><span class="sr-only">Ações</span></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white  divide-y divide-gray-200 ">
                        @forelse ($analyses as $item)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $item->analysis_date->format('d/m/Y') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ number_format($item->total_listings) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ number_format($item->br_listings) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ number_format($item->us_listings) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ number_format($item->gb_listings) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ number_format($item->de_listings) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ number_format($item->fr_listings) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ number_format($item->it_listings) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ number_format($item->jp_listings) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ number_format($item->ca_listings) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ number_format($item->be_listings) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ number_format($item->se_listings) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                <button class="text-indigo-600 hover:text-indigo-900  transition btn-edit"
                                        data-id="{{ $item->id }}"
                                        data-action="{{ route('admin.market-analysis.update', $item->id) }}"
                                        data-analysis='{{ $item->toJson() }}'>Editar</button>
                                <form action="{{ route('admin.market-analysis.destroy', $item->id) }}" method="POST" class="inline" onsubmit="return confirm('Tem certeza que deseja excluir?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900  transition">Excluir</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="13" class="px-6 py-12 text-center text-sm text-gray-500">Nenhum registro encontrado. Adicione o primeiro acima.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
             @if ($analyses->hasPages())
                <div class="p-6 border-t border-gray-200 ">
                    {{ $analyses->links() }}
                </div>
            @endif
        </div>

        <!-- Seção de Gráficos -->
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
            <div class="bg-white  shadow-lg rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 ">Evolução de Listings (Mundo)</h3>
                <div class="mt-4" style="height: 350px;"><canvas id="totalListingsChart"></canvas></div>
            </div>
            <div class="bg-white  shadow-lg rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 ">Evolução por País</h3>
                <div class="mt-4" style="height: 350px;"><canvas id="countryListingsChart"></canvas></div>
            </div>
        </div>
    </div>

    <!-- Modal de Edição -->
    <div id="editModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-3xl shadow-lg rounded-md bg-white ">
            <div class="flex justify-between items-center pb-3 border-b ">
                <p class="text-2xl font-bold text-gray-900 ">Editar Análise de Mercado</p>
                <button id="closeModal" class="cursor-pointer z-50 text-gray-400 hover:text-gray-700 ">
                    <svg class="fill-current" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
                </button>
            </div>
            <div class="mt-5">
                <form id="editForm" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')
                    @include('admin.market-analysis._form', ['analysis' => new \App\Models\MarketAnalysis()])
                    <div class="flex justify-end pt-6 border-t  space-x-4">
                        <button type="button" id="cancelModal" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">Cancelar</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"><i class="fas fa-save mr-2"></i>Salvar Alterações</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        // Lógica de Coleta Automática
        const autoCollectBtn = document.getElementById('autoCollectBtn');
        const autoCollectStatus = document.getElementById('autoCollectStatus');

        autoCollectBtn.addEventListener('click', async function() {
            const originalText = this.innerHTML;
            this.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Coletando...';
            this.disabled = true;

            autoCollectStatus.className = 'mb-4 p-4 rounded-md';
            autoCollectStatus.classList.remove('hidden');

            try {
                const response = await fetch('{{ route("admin.market-analysis.auto-collect") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    autoCollectStatus.className += ' bg-green-100 border-l-4 border-green-500 text-green-700';
                    autoCollectStatus.innerHTML = '<p><i class="fas fa-check mr-2"></i>' + data.message + '</p>';

                    // Recarregar a página após 2 segundos para mostrar os novos dados
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                } else {
                    autoCollectStatus.className += ' bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700';
                    autoCollectStatus.innerHTML = '<p><i class="fas fa-exclamation-triangle mr-2"></i>' + data.message + '</p>';
                }
            } catch (error) {
                autoCollectStatus.className += ' bg-red-100 border-l-4 border-red-500 text-red-700';
                autoCollectStatus.innerHTML = '<p><i class="fas fa-times mr-2"></i>Erro na requisição: ' + error.message + '</p>';
            } finally {
                this.innerHTML = originalText;
                this.disabled = false;
            }
        });

        // Lógica do Modal de Edição
        const modal = document.getElementById('editModal');
        const closeModalBtn = document.getElementById('closeModal');
        const cancelModalBtn = document.getElementById('cancelModal');
        const editForm = document.getElementById('editForm');

        document.querySelectorAll('.btn-edit').forEach(button => {
            button.addEventListener('click', function() {
                const action = this.dataset.action;
                const analysis = JSON.parse(this.dataset.analysis);

                editForm.setAttribute('action', action);

                // Preencher cada campo do formulário no modal
                editForm.querySelector('[name="analysis_date"]').value = analysis.analysis_date.split('T')[0];
                for (const key in analysis) {
                    if (editForm.querySelector(`[name="${key}"]`)) {
                        editForm.querySelector(`[name="${key}"]`).value = analysis[key];
                    }
                }
                modal.classList.remove('hidden');
            });
        });

        const closeModal = () => modal.classList.add('hidden');
        closeModalBtn.addEventListener('click', closeModal);
        cancelModalBtn.addEventListener('click', closeModal);
        modal.addEventListener('click', (e) => {
            if (e.target.id === 'editModal') {
                closeModal();
            }
        });

        // Lógica dos Gráficos
        const chartData = @json($chartData);
        if(chartData.labels && chartData.labels.length > 0) {
            const chartOptions = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { labels: { color: '#333' }}},
                scales: {
                    x: { ticks: { color: '#666' }, grid: { color: 'rgba(128,128,128,0.2)' } },
                    y: { ticks: { color: '#666' }, grid: { color: 'rgba(128,128,128,0.2)' } }
                }
            };
            // Gráfico Total
            new Chart(document.getElementById('totalListingsChart'), {
                type: 'line',
                data: {
                    labels: chartData.labels,
                    datasets: [{
                        label: 'Total Listings (Mundo)',
                        data: chartData.total_listings,
                        borderColor: 'rgba(59, 130, 246, 1)',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        fill: true,
                        tension: 0.2
                    }]
                },
                options: chartOptions
            });

            // Gráfico por País
            new Chart(document.getElementById('countryListingsChart'), {
                type: 'line',
                data: {
                    labels: chartData.labels,
                    datasets: [
                        { label: 'Brasil', data: chartData.br_listings, borderColor: '#10B981', tension: 0.2 },
                        { label: 'EUA', data: chartData.us_listings, borderColor: '#EF4444', tension: 0.2 },
                        { label: 'Reino Unido', data: chartData.gb_listings, borderColor: '#F59E0B', tension: 0.2 },
                        { label: 'Alemanha', data: chartData.de_listings, borderColor: '#3B82F6', tension: 0.2 },
                        { label: 'França', data: chartData.fr_listings, borderColor: '#8B5CF6', tension: 0.2 },
                        { label: 'Itália', data: chartData.it_listings, borderColor: '#F97316', tension: 0.2 },
                        { label: 'Japão', data: chartData.jp_listings, borderColor: '#EC4899', tension: 0.2 },
                        { label: 'Canadá', data: chartData.ca_listings, borderColor: '#06B6D4', tension: 0.2 },
                        { label: 'Bélgica', data: chartData.be_listings, borderColor: '#84CC16', tension: 0.2 },
                        { label: 'Suécia', data: chartData.se_listings, borderColor: '#6366F1', tension: 0.2 },
                    ]
                },
                options: chartOptions
            });
        }
    });
    </script>
    @endpush
</x-admin-layout>
