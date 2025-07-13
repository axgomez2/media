<x-admin-layout>
    <!-- Header -->
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-gray-800">📈 Gráficos - Análise de Mercado</h2>
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
        <form method="GET" action="{{ route('admin.market-analysis.charts') }}">
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

    <!-- Gráficos de Linha -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Evolução dos Preços -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">Evolução dos Preços</h3>
            </div>
            <div class="p-6">
                <canvas id="priceChart" class="w-full h-64"></canvas>
            </div>
        </div>

        <!-- Evolução das Listagens -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">Evolução das Listagens</h3>
            </div>
            <div class="p-6">
                <canvas id="listingsChart" class="w-full h-64"></canvas>
            </div>
        </div>
    </div>

    <!-- Gráficos de Pizza/Barras -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Distribuição por Categoria -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">Distribuição por Categoria</h3>
            </div>
            <div class="p-6">
                <canvas id="categoryChart" class="w-full h-64"></canvas>
            </div>
        </div>

        <!-- Distribuição por País -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">Distribuição por País</h3>
            </div>
            <div class="p-6">
                <canvas id="countryChart" class="w-full h-64"></canvas>
            </div>
        </div>

        <!-- Faixas de Preço -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">Faixas de Preço</h3>
            </div>
            <div class="p-6">
                <canvas id="priceRangeChart" class="w-full h-64"></canvas>
            </div>
        </div>

        <!-- Distribuição por Década -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">Distribuição por Década</h3>
            </div>
            <div class="p-6">
                <canvas id="decadeChart" class="w-full h-64"></canvas>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Dados dos gráficos
        const priceData = @json($chartData['price_evolution']);
        const listingsData = @json($chartData['listings_evolution']);
        const categoryData = @json($categoryData);
        const countryData = @json($countryData);
        const priceRangesData = @json($priceRangesData);
        const decadeData = @json($decadeData);

        // Configuração de cores
        const colors = {
            primary: 'rgb(54, 162, 235)',
            success: 'rgb(75, 192, 192)',
            warning: 'rgb(255, 205, 86)',
            danger: 'rgb(255, 99, 132)',
            info: 'rgb(153, 102, 255)',
            secondary: 'rgb(201, 203, 207)'
        };

        // Gráfico de Evolução dos Preços
        const priceCtx = document.getElementById('priceChart').getContext('2d');
        const priceChart = new Chart(priceCtx, {
            type: 'line',
            data: priceData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'R$ ' + value.toFixed(2);
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': R$ ' + context.parsed.y.toFixed(2);
                            }
                        }
                    }
                }
            }
        });

        // Gráfico de Evolução das Listagens
        const listingsCtx = document.getElementById('listingsChart').getContext('2d');
        const listingsChart = new Chart(listingsCtx, {
            type: 'line',
            data: listingsData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString();
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + context.parsed.y.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        // Gráfico de Categorias
        const categoryCtx = document.getElementById('categoryChart').getContext('2d');
        const categoryChart = new Chart(categoryCtx, {
            type: 'doughnut',
            data: {
                labels: Object.keys(categoryData).slice(0, 10),
                datasets: [{
                    data: Object.values(categoryData).slice(0, 10),
                    backgroundColor: [
                        colors.primary,
                        colors.success,
                        colors.warning,
                        colors.danger,
                        colors.info,
                        colors.secondary,
                        'rgb(255, 159, 64)',
                        'rgb(199, 199, 199)',
                        'rgb(83, 102, 255)',
                        'rgb(255, 99, 255)'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Gráfico de Países
        const countryCtx = document.getElementById('countryChart').getContext('2d');
        const countryChart = new Chart(countryCtx, {
            type: 'bar',
            data: {
                labels: Object.keys(countryData).slice(0, 10),
                datasets: [{
                    label: 'Listagens',
                    data: Object.values(countryData).slice(0, 10),
                    backgroundColor: colors.primary,
                    borderColor: colors.primary,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString();
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });

        // Gráfico de Faixas de Preço
        const priceRangeCtx = document.getElementById('priceRangeChart').getContext('2d');
        const priceRangeChart = new Chart(priceRangeCtx, {
            type: 'bar',
            data: {
                labels: Object.keys(priceRangesData),
                datasets: [{
                    label: 'Listagens',
                    data: Object.values(priceRangesData),
                    backgroundColor: colors.success,
                    borderColor: colors.success,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString();
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });

        // Gráfico de Décadas
        const decadeCtx = document.getElementById('decadeChart').getContext('2d');
        const decadeChart = new Chart(decadeCtx, {
            type: 'bar',
            data: {
                labels: Object.keys(decadeData),
                datasets: [{
                    label: 'Listagens',
                    data: Object.values(decadeData),
                    backgroundColor: colors.warning,
                    borderColor: colors.warning,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString();
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    </script>
</x-admin-layout>
