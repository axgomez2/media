<x-admin-layout title="Relatório de Clientes">
<div class="container px-6 mx-auto grid">
    <div class="flex justify-between items-center">
        <h2 class="my-6 text-2xl font-semibold text-gray-700">
            Relatório de Clientes
        </h2>
        <a href="{{ route('admin.reports.index') }}" class="px-4 py-2 text-sm text-indigo-600 bg-indigo-100 rounded-lg hover:bg-indigo-200">
            &larr; Voltar para Relatórios
        </a>
    </div>

    <!-- Mensagens de sessão e erros de validação -->
    <x-admin.session-messages />
    <x-admin.validation-errors />

    <!-- Cards de estatísticas -->
    <div class="grid gap-6 mb-8 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-6">
        <x-admin.stats-card
            title="Total de Clientes"
            :value="$stats['total_clients']"
            icon-color="indigo"
            :icon="'<path d=&quot;M9 12a2 2 0 100-4 2 2 0 000 4z&quot;></path><path fill-rule=&quot;evenodd&quot; d=&quot;M10 18a8 8 0 100-16 8 8 0 000 16zm0-2a6 6 0 100-12 6 6 0 000 12z&quot; clip-rule=&quot;evenodd&quot;></path>'" />

        <x-admin.stats-card
            title="Novos no Mês"
            :value="$stats['new_this_month']"
            icon-color="green"
            :icon="'<path fill-rule=&quot;evenodd&quot; d=&quot;M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z&quot; clip-rule=&quot;evenodd&quot;></path>'" />

        <x-admin.stats-card
            title="Clientes Ativos"
            :value="$stats['active_clients']"
            icon-color="orange"
            :icon="'<path fill-rule=&quot;evenodd&quot; d=&quot;M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.293l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L9 9.414V13a1 1 0 102 0V9.414l1.293 1.293a1 1 0 001.414-1.414z&quot; clip-rule=&quot;evenodd&quot;></path>'" />

        <x-admin.stats-card
            title="Taxa de Verificação"
            :value="number_format($stats['verification_rate'], 1) . '%'"
            icon-color="blue"
            :icon="'<path fill-rule=&quot;evenodd&quot; d=&quot;M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z&quot; clip-rule=&quot;evenodd&quot;></path>'" />

        <x-admin.stats-card
            title="Taxa de Conversão"
            :value="number_format($stats['conversion_rate'], 1) . '%'"
            icon-color="purple"
            :icon="'<path fill-rule=&quot;evenodd&quot; d=&quot;M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3zm11.707 4.707a1 1 0 00-1.414-1.414L10 9.586 8.707 8.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z&quot; clip-rule=&quot;evenodd&quot;></path>'" />

        <x-admin.stats-card
            title="Valor Médio por Cliente"
            :value="'R$ ' . number_format($stats['average_value_per_client'], 2, ',', '.')"
            icon-color="yellow"
            :icon="'<path d=&quot;M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z&quot;></path><path fill-rule=&quot;evenodd&quot; d=&quot;M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z&quot; clip-rule=&quot;evenodd&quot;></path>'" />
    </div>

    <!-- Filtros e busca -->
    <div class="p-4 bg-white rounded-lg shadow-md mb-8">
        <form method="GET" action="{{ route('admin.reports.clients.index') }}" class="space-y-4">
            <div class="grid gap-4 grid-cols-1 md:grid-cols-2 lg:grid-cols-4">
                <!-- Campo de busca -->
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">
                        Buscar por nome ou email
                        <span class="text-xs text-gray-500 font-normal ml-1">(Ctrl+K)</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <input type="text"
                               id="search"
                               name="search"
                               value="{{ request('search') }}"
                               placeholder="Digite o nome ou email..."
                               class="w-full pl-10 pr-10 py-2 border {{ $errors->has('search') ? 'border-red-300' : 'border-gray-300' }} rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200">
                        @if(request('search'))
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <button type="button"
                                        onclick="document.getElementById('search').value=''; document.getElementById('search').form.submit();"
                                        class="text-gray-400 hover:text-gray-600 transition-colors duration-150"
                                        title="Limpar busca">
                                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                    </svg>
                                </button>
                            </div>
                        @endif
                    </div>
                    <p class="mt-1 text-xs text-gray-500">
                        Digite pelo menos 2 caracteres para buscar automaticamente
                    </p>
                    <x-admin.form-error field="search" />
                </div>

                <!-- Filtro por verificação -->
                <div>
                    <label for="verified" class="block text-sm font-medium text-gray-700 mb-1">
                        Status de Verificação
                    </label>
                    <select id="verified"
                            name="verified"
                            class="w-full px-3 py-2 border {{ $errors->has('verified') ? 'border-red-300' : 'border-gray-300' }} rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="all" {{ request('verified') === 'all' ? 'selected' : '' }}>Todos</option>
                        <option value="verified" {{ request('verified') === 'verified' ? 'selected' : '' }}>Verificados</option>
                        <option value="unverified" {{ request('verified') === 'unverified' ? 'selected' : '' }}>Não Verificados</option>
                    </select>
                    <x-admin.form-error field="verified" />
                </div>

                <!-- Filtro por status da conta -->
                <div>
                    <label for="account_status" class="block text-sm font-medium text-gray-700 mb-1">
                        Status da Conta
                    </label>
                    <select id="account_status"
                            name="account_status"
                            class="w-full px-3 py-2 border {{ $errors->has('account_status') ? 'border-red-300' : 'border-gray-300' }} rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="all" {{ request('account_status') === 'all' ? 'selected' : '' }}>Todos</option>
                        <option value="active" {{ request('account_status') === 'active' ? 'selected' : '' }}>Ativos</option>
                        <option value="inactive" {{ request('account_status') === 'inactive' ? 'selected' : '' }}>Inativos</option>
                    </select>
                    <x-admin.form-error field="account_status" />
                </div>

                <!-- Filtro por período -->
                <div>
                    <label for="period" class="block text-sm font-medium text-gray-700 mb-1">
                        Período de Cadastro
                    </label>
                    <select id="period"
                            name="period"
                            class="w-full px-3 py-2 border {{ $errors->has('period') ? 'border-red-300' : 'border-gray-300' }} rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="all" {{ request('period') === 'all' ? 'selected' : '' }}>Todos</option>
                        <option value="today" {{ request('period') === 'today' ? 'selected' : '' }}>Hoje</option>
                        <option value="week" {{ request('period') === 'week' ? 'selected' : '' }}>Esta Semana</option>
                        <option value="month" {{ request('period') === 'month' ? 'selected' : '' }}>Este Mês</option>
                        <option value="year" {{ request('period') === 'year' ? 'selected' : '' }}>Este Ano</option>
                    </select>
                    <x-admin.form-error field="period" />
                </div>

                <!-- Itens por página -->
                <div>
                    <label for="per_page" class="block text-sm font-medium text-gray-700 mb-1">
                        Itens por página
                    </label>
                    <select id="per_page"
                            name="per_page"
                            class="w-full px-3 py-2 border {{ $errors->has('per_page') ? 'border-red-300' : 'border-gray-300' }} rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page', 50) == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                    </select>
                    <x-admin.form-error field="per_page" />
                </div>
            </div>

            <div class="flex flex-wrap gap-2 items-center">
                <button type="submit"
                        class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    Filtrar
                </button>

                @if(request()->hasAny(['search', 'verified', 'account_status', 'period', 'start_date', 'end_date']))
                    <a href="{{ route('admin.reports.clients.index') }}"
                       class="px-4 py-2 bg-gray-500 text-white text-sm font-medium rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Limpar Filtros
                    </a>
                @endif

                <a href="{{ route('admin.reports.clients.export', request()->query()) }}"
                   class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Exportar CSV
                </a>
            </div>
        </form>

        <!-- Badges de filtros ativos -->
        @if(request()->hasAny(['search', 'verified', 'account_status', 'period']))
            <div class="mt-4 p-3 bg-gray-50 rounded-lg border border-gray-200">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-sm font-medium text-gray-700 flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.414A1 1 0 013 6.707V4z"></path>
                        </svg>
                        Filtros ativos:
                    </span>

                @if(request('search'))
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 transition-all duration-200 hover:bg-indigo-200">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        Busca: "{{ Str::limit(request('search'), 20) }}"
                        <a href="{{ request()->fullUrlWithQuery(['search' => null]) }}"
                           class="filter-badge-remove ml-1 text-indigo-600 hover:text-indigo-800 hover:bg-indigo-200 rounded-full p-0.5 transition-colors duration-150"
                           title="Remover filtro de busca">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                            </svg>
                        </a>
                    </span>
                @endif

                @if(request('verified') && request('verified') !== 'all')
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 transition-all duration-200 hover:bg-blue-200">
                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            @if(request('verified') === 'verified')
                                <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            @else
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            @endif
                        </svg>
                        {{ request('verified') === 'verified' ? 'Verificados' : 'Não Verificados' }}
                        <a href="{{ request()->fullUrlWithQuery(['verified' => 'all']) }}"
                           class="filter-badge-remove ml-1 text-blue-600 hover:text-blue-800 hover:bg-blue-200 rounded-full p-0.5 transition-colors duration-150"
                           title="Remover filtro de verificação">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                            </svg>
                        </a>
                    </span>
                @endif

                @if(request('account_status') && request('account_status') !== 'all')
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ request('account_status') === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }} transition-all duration-200 hover:{{ request('account_status') === 'active' ? 'bg-green-200' : 'bg-red-200' }}">
                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            @if(request('account_status') === 'active')
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            @else
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                            @endif
                        </svg>
                        {{ request('account_status') === 'active' ? 'Contas Ativas' : 'Contas Inativas' }}
                        <a href="{{ request()->fullUrlWithQuery(['account_status' => 'all']) }}"
                           class="filter-badge-remove ml-1 {{ request('account_status') === 'active' ? 'text-green-600 hover:text-green-800 hover:bg-green-200' : 'text-red-600 hover:text-red-800 hover:bg-red-200' }} rounded-full p-0.5 transition-colors duration-150"
                           title="Remover filtro de status da conta">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                            </svg>
                        </a>
                    </span>
                @endif

                @if(request('period') && request('period') !== 'all')
                    @php
                        $periodLabels = [
                            'today' => 'Hoje',
                            'week' => 'Esta Semana',
                            'month' => 'Este Mês',
                            'year' => 'Este Ano'
                        ];
                        $periodIcons = [
                            'today' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>',
                            'week' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>',
                            'month' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>',
                            'year' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>'
                        ];
                    @endphp
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 transition-all duration-200 hover:bg-green-200">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            {!! $periodIcons[request('period')] ?? $periodIcons['month'] !!}
                        </svg>
                        {{ $periodLabels[request('period')] ?? request('period') }}
                        <a href="{{ request()->fullUrlWithQuery(['period' => 'all']) }}"
                           class="filter-badge-remove ml-1 text-green-600 hover:text-green-800 hover:bg-green-200 rounded-full p-0.5 transition-colors duration-150"
                           title="Remover filtro de período">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                            </svg>
                        </a>
                    </span>
                @endif

                <!-- Botão para limpar todos os filtros -->
                <a href="{{ route('admin.reports.clients.index') }}"
                   class="inline-flex items-center px-3 py-1 text-xs font-medium text-gray-600 bg-white border border-gray-300 rounded-md hover:bg-gray-50 hover:text-gray-700 transition-colors duration-150"
                   title="Limpar todos os filtros">
                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    Limpar todos
                </a>
                </div>
            </div>
        @endif
    </div>

    <!-- Resumo dos resultados -->
    @if(request()->hasAny(['search', 'verified', 'account_status', 'period']))
        <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div class="text-sm">
                    <span class="font-medium text-blue-800">
                        {{ $clients->total() }} {{ $clients->total() === 1 ? 'cliente encontrado' : 'clientes encontrados' }}
                    </span>
                    @if(request('search'))
                        <span class="text-blue-700">
                            para a busca "{{ request('search') }}"
                        </span>
                    @endif
                    @if(request('verified') && request('verified') !== 'all')
                        <span class="text-blue-700">
                            • {{ request('verified') === 'verified' ? 'verificados' : 'não verificados' }}
                        </span>
                    @endif
                    @if(request('account_status') && request('account_status') !== 'all')
                        <span class="text-blue-700">
                            • {{ request('account_status') === 'active' ? 'contas ativas' : 'contas inativas' }}
                        </span>
                    @endif
                    @if(request('period') && request('period') !== 'all')
                        @php
                            $periodLabels = [
                                'today' => 'cadastrados hoje',
                                'week' => 'cadastrados esta semana',
                                'month' => 'cadastrados este mês',
                                'year' => 'cadastrados este ano'
                            ];
                        @endphp
                        <span class="text-blue-700">
                            • {{ $periodLabels[request('period')] ?? request('period') }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <!-- Tabela de clientes -->
    <div class="p-4 bg-white rounded-lg shadow-md">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-700">
                Lista de Clientes
                @if(!request()->hasAny(['search', 'verified', 'account_status', 'period']))
                    <span class="text-sm text-gray-500">({{ $clients->total() }} {{ $clients->total() === 1 ? 'cliente' : 'clientes' }})</span>
                @endif
            </h3>
        </div>

        @if($clients->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full whitespace-nowrap">
                    <thead>
                        <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b bg-gray-50">
                            <th class="px-4 py-3">Cliente</th>
                            <th class="px-4 py-3">Email</th>
                            <th class="px-4 py-3">Email Status</th>
                            <th class="px-4 py-3">Conta Status</th>
                            <th class="px-4 py-3">Data de Cadastro</th>
                            <th class="px-4 py-3">Pedidos</th>
                            <th class="px-4 py-3">Wishlist</th>
                            <th class="px-4 py-3">Carrinho</th>
                            <th class="px-4 py-3">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y">
                        @foreach($clients as $client)
                            <tr class="text-gray-700 hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <div class="flex items-center text-sm">
                                        <div class="relative hidden mr-3 md:block">
                                            <x-admin.clients.avatar :client="$client" size="sm" />
                                            <div class="absolute inset-0 rounded-full shadow-inner" aria-hidden="true"></div>
                                        </div>
                                        <div>
                                            <p class="font-semibold">{{ $client->name }}</p>
                                            <p class="text-xs text-gray-600">
                                                ID: {{ $client->id }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    {{ $client->email }}
                                </td>
                                <td class="px-4 py-3 text-xs">
                                    <x-admin.status-badge
                                        :type="$client->is_verified ? 'verified' : 'unverified'"
                                        :text="$client->is_verified ? 'Verificado' : 'Pendente'"
                                        size="xs" />
                                </td>
                                <td class="px-4 py-3 text-xs">
                                    <x-admin.status-badge
                                        id="status-badge-{{ $client->id }}"
                                        :type="$client->status === 'active' ? 'active' : 'inactive'"
                                        :text="$client->status === 'active' ? 'Ativo' : 'Inativo'"
                                        size="xs" />
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <div>
                                        <p>{{ $client->created_at->format('d/m/Y') }}</p>
                                        <p class="text-xs text-gray-500">{{ $client->created_at->format('H:i') }}</p>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <span class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">
                                        {{ $client->orders_count }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <span class="px-2 py-1 text-xs font-medium bg-purple-100 text-purple-800 rounded-full">
                                        {{ $client->wishlists_count }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">
                                        {{ $client->cart_items_count }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center space-x-2">
                                        <a href="{{ route('admin.reports.clients.show', $client->id) }}"
                                           class="px-2 py-1 text-xs font-medium text-indigo-600 bg-indigo-100 rounded hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                           title="Ver detalhes">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                        </a>

                                        <button onclick="toggleClientStatus('{{ $client->id }}', '{{ $client->status === 'active' ? 'inactive' : 'active' }}')"
                                                id="status-btn-{{ $client->id }}"
                                                class="px-2 py-1 text-xs font-medium rounded focus:outline-none focus:ring-2 {{ $client->status === 'active' ? 'text-red-600 bg-red-100 hover:bg-red-200 focus:ring-red-500' : 'text-green-600 bg-green-100 hover:bg-green-200 focus:ring-green-500' }}"
                                                title="{{ $client->status === 'active' ? 'Desativar cliente' : 'Ativar cliente' }}">
                                            @if($client->status === 'active')
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728"></path>
                                                </svg>
                                            @else
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                            @endif
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Paginação -->
            <div class="mt-6">
                {{ $clients->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhum cliente encontrado</h3>
                <p class="mt-1 text-sm text-gray-500">
                    @if(request()->hasAny(['search', 'verified', 'account_status', 'period']))
                        Tente ajustar os filtros para encontrar clientes.
                    @else
                        Não há clientes cadastrados no sistema ainda.
                    @endif
                </p>
                @if(request()->hasAny(['search', 'verified', 'account_status', 'period']))
                    <div class="mt-6">
                        <a href="{{ route('admin.reports.clients.index') }}"
                           class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Limpar filtros
                        </a>
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>

<!-- JavaScript para busca em tempo real e filtros -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search');
    const verifiedSelect = document.getElementById('verified');
    const periodSelect = document.getElementById('period');
    const perPageSelect = document.getElementById('per_page');
    const form = searchInput.closest('form');
    const filterButton = form.querySelector('button[type="submit"]');

    let searchTimeout;
    let isSubmitting = false;

    // Função para atualizar URL sem recarregar a página
    function updateURL(params) {
        const url = new URL(window.location);

        // Limpar parâmetros existentes
        url.searchParams.delete('search');
        url.searchParams.delete('verified');
        url.searchParams.delete('period');
        url.searchParams.delete('per_page');
        url.searchParams.delete('page'); // Reset pagination

        // Adicionar novos parâmetros
        Object.keys(params).forEach(key => {
            if (params[key] && params[key] !== 'all' && params[key] !== '') {
                url.searchParams.set(key, params[key]);
            }
        });

        // Atualizar URL no histórico
        window.history.pushState({}, '', url);
    }

    // Função para mostrar loading state
    function showLoading() {
        if (filterButton) {
            filterButton.disabled = true;
            filterButton.innerHTML = `
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Filtrando...
            `;
        }
        isSubmitting = true;
    }

    // Função para esconder loading state
    function hideLoading() {
        if (filterButton) {
            filterButton.disabled = false;
            filterButton.innerHTML = `
                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                Filtrar
            `;
        }
        isSubmitting = false;
    }

    // Função para submeter form com loading
    function submitFormWithLoading() {
        if (isSubmitting) return;

        showLoading();

        // Atualizar URL antes de submeter
        const params = {
            search: searchInput.value,
            verified: verifiedSelect.value,
            period: periodSelect.value,
            per_page: perPageSelect.value
        };
        updateURL(params);

        // Submeter form
        setTimeout(() => {
            form.submit();
        }, 100);
    }

    // Busca em tempo real com debounce melhorado
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);

        // Mostrar feedback visual imediato
        searchInput.classList.add('border-indigo-300', 'ring-1', 'ring-indigo-200');

        searchTimeout = setTimeout(function() {
            // Remover feedback visual
            searchInput.classList.remove('border-indigo-300', 'ring-1', 'ring-indigo-200');

            // Submeter se tiver pelo menos 2 caracteres ou estiver vazio
            if (searchInput.value.length >= 2 || searchInput.value.length === 0) {
                submitFormWithLoading();
            }
        }, 600); // Aumentado para 600ms para melhor UX
    });

    // Auto-submit nos selects com loading
    [verifiedSelect, periodSelect, perPageSelect].forEach(function(select) {
        select.addEventListener('change', function() {
            // Feedback visual
            select.classList.add('border-indigo-300', 'ring-1', 'ring-indigo-200');

            setTimeout(() => {
                select.classList.remove('border-indigo-300', 'ring-1', 'ring-indigo-200');
                submitFormWithLoading();
            }, 200);
        });
    });

    // Interceptar submit do form
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        submitFormWithLoading();
    });

    // Gerenciar botões de voltar/avançar do navegador
    window.addEventListener('popstate', function(e) {
        location.reload();
    });

    // Adicionar funcionalidade aos badges de filtros ativos
    document.querySelectorAll('.filter-badge-remove').forEach(function(badge) {
        badge.addEventListener('click', function(e) {
            e.preventDefault();
            showLoading();

            setTimeout(() => {
                window.location.href = badge.href;
            }, 100);
        });
    });

    // Funcionalidade para limpar todos os filtros
    const clearFiltersBtn = document.querySelector('a[href*="admin.reports.clients.index"]:not([href*="?"])');
    if (clearFiltersBtn && clearFiltersBtn.textContent.includes('Limpar')) {
        clearFiltersBtn.addEventListener('click', function(e) {
            e.preventDefault();
            showLoading();

            // Limpar campos do formulário
            searchInput.value = '';
            verifiedSelect.value = 'all';
            periodSelect.value = 'all';
            perPageSelect.value = '50';

            // Atualizar URL
            updateURL({});

            setTimeout(() => {
                window.location.href = this.href;
            }, 100);
        });
    }

    // Sistema de tooltips avançado
    function initTooltips() {
        const tooltipElements = [
            { element: searchInput, text: 'Digite pelo menos 2 caracteres para buscar automaticamente. Use Ctrl+K para focar rapidamente.' },
            { element: verifiedSelect, text: 'Filtrar clientes por status de verificação de email' },
            { element: periodSelect, text: 'Filtrar clientes por período de cadastro no sistema' },
            { element: perPageSelect, text: 'Definir quantidade de clientes exibidos por página' },
            { element: document.querySelector('a[href*="export"]'), text: 'Exportar dados dos clientes filtrados para arquivo CSV' }
        ];

        tooltipElements.forEach(function(tooltipData) {
            if (tooltipData.element) {
                createTooltip(tooltipData.element, tooltipData.text);
            }
        });

        // Tooltips para botões de ação na tabela
        document.querySelectorAll('[title]').forEach(function(element) {
            if (element.title) {
                createTooltip(element, element.title);
                element.removeAttribute('title'); // Remove o title padrão
            }
        });
    }

    function createTooltip(element, text) {
        let tooltip = null;
        let showTimeout = null;
        let hideTimeout = null;

        element.addEventListener('mouseenter', function(e) {
            clearTimeout(hideTimeout);
            showTimeout = setTimeout(() => {
                // Remove tooltip existente
                if (tooltip) {
                    tooltip.remove();
                }

                // Criar novo tooltip
                tooltip = document.createElement('div');
                tooltip.className = 'tooltip-custom fixed z-50 px-3 py-2 text-sm text-white bg-gray-900 rounded-lg shadow-lg pointer-events-none transition-opacity duration-200 opacity-0';
                tooltip.textContent = text;
                tooltip.style.maxWidth = '250px';
                tooltip.style.wordWrap = 'break-word';

                document.body.appendChild(tooltip);

                // Posicionar tooltip
                const rect = element.getBoundingClientRect();
                const tooltipRect = tooltip.getBoundingClientRect();

                let left = rect.left + (rect.width / 2) - (tooltipRect.width / 2);
                let top = rect.top - tooltipRect.height - 8;

                // Ajustar se sair da tela
                if (left < 8) left = 8;
                if (left + tooltipRect.width > window.innerWidth - 8) {
                    left = window.innerWidth - tooltipRect.width - 8;
                }
                if (top < 8) {
                    top = rect.bottom + 8;
                }

                tooltip.style.left = left + 'px';
                tooltip.style.top = top + 'px';

                // Mostrar tooltip
                setTimeout(() => {
                    if (tooltip) {
                        tooltip.classList.remove('opacity-0');
                        tooltip.classList.add('opacity-100');
                    }
                }, 50);
            }, 500); // Delay de 500ms antes de mostrar
        });

        element.addEventListener('mouseleave', function() {
            clearTimeout(showTimeout);
            if (tooltip) {
                hideTimeout = setTimeout(() => {
                    if (tooltip) {
                        tooltip.classList.remove('opacity-100');
                        tooltip.classList.add('opacity-0');
                        setTimeout(() => {
                            if (tooltip) {
                                tooltip.remove();
                                tooltip = null;
                            }
                        }, 200);
                    }
                }, 100);
            }
        });
    }

    // Inicializar tooltips
    initTooltips();

    // Adicionar indicador visual para campos com valores
    function updateFieldIndicators() {
        [searchInput, verifiedSelect, periodSelect].forEach(function(field) {
            const hasValue = field.value && field.value !== 'all' && field.value !== '';

            if (hasValue) {
                field.classList.add('bg-indigo-50', 'border-indigo-300');
            } else {
                field.classList.remove('bg-indigo-50', 'border-indigo-300');
            }
        });
    }

    // Atualizar indicadores na inicialização
    updateFieldIndicators();

    // Atualizar indicadores quando campos mudarem
    [searchInput, verifiedSelect, periodSelect].forEach(function(field) {
        field.addEventListener('input', updateFieldIndicators);
        field.addEventListener('change', updateFieldIndicators);
    });

    // Adicionar atalhos de teclado
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + K para focar na busca
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            searchInput.focus();
            searchInput.select();
        }

        // Escape para limpar busca
        if (e.key === 'Escape' && document.activeElement === searchInput) {
            searchInput.value = '';
            searchInput.blur();
            submitFormWithLoading();
        }
    });

    // Adicionar placeholder dinâmico
    const placeholders = [
        'Digite o nome do cliente...',
        'Busque por email...',
        'Nome ou email do cliente...',
        'Encontre um cliente...'
    ];

    let placeholderIndex = 0;
    setInterval(function() {
        if (document.activeElement !== searchInput && !searchInput.value) {
            searchInput.placeholder = placeholders[placeholderIndex];
            placeholderIndex = (placeholderIndex + 1) % placeholders.length;
        }
    }, 3000);

    // Add keyboard shortcuts for better UX
    document.addEventListener('keydown', function(e) {
        // Alt + E para exportar
        if (e.altKey && e.key === 'e') {
            e.preventDefault();
            const exportBtn = document.querySelector('a[href*="export"]');
            if (exportBtn) {
                showLoadingOverlay('Preparando exportação...');
                setTimeout(() => {
                    window.location.href = exportBtn.href;
                    hideLoadingOverlay();
                }, 1000);
            }
        }

        // Alt + C para limpar filtros
        if (e.altKey && e.key === 'c') {
            e.preventDefault();
            const clearBtn = document.querySelector('a[href*="admin.reports.clients.index"]:not([href*="?"])');
            if (clearBtn) {
                showLoadingOverlay('Limpando filtros...');
                setTimeout(() => {
                    window.location.href = clearBtn.href;
                }, 500);
            }
        }

        // Alt + R para atualizar página
        if (e.altKey && e.key === 'r') {
            e.preventDefault();
            showLoadingOverlay('Atualizando dados...');
            setTimeout(() => {
                window.location.reload();
            }, 500);
        }
    });

    // Add visual feedback for table rows
    document.querySelectorAll('tbody tr').forEach(function(row) {
        row.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.01)';
            this.style.boxShadow = '0 4px 8px rgba(0,0,0,0.1)';
            this.style.transition = 'all 0.2s ease';
        });

        row.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
            this.style.boxShadow = 'none';
        });
    });

    // Add loading animation to export button
    const exportBtn = document.querySelector('a[href*="export"]');
    if (exportBtn) {
        exportBtn.addEventListener('click', function(e) {
            const originalText = this.innerHTML;
            this.innerHTML = `
                <svg class="w-4 h-4 inline mr-1 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Exportando...
            `;

            setTimeout(() => {
                this.innerHTML = originalText;
            }, 3000);
        });

        // Add keyboard shortcut hint
        exportBtn.title = exportBtn.title + ' (Alt+E)';
    }
});
</script>

<!-- Estilos adicionais para animações e transições -->
<style>
    /* Animação para campos de busca */
    .search-loading {
        background-image: linear-gradient(90deg, transparent, rgba(99, 102, 241, 0.1), transparent);
        background-size: 200% 100%;
        animation: shimmer 1.5s infinite;
    }

    @keyframes shimmer {
        0% { background-position: -200% 0; }
        100% { background-position: 200% 0; }
    }

    /* Transições suaves para badges */
    .filter-badge-remove {
        transition: all 0.15s ease-in-out;
    }

    .filter-badge-remove:hover {
        transform: scale(1.1);
    }

    /* Indicador visual para campos ativos */
    .field-active {
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        border-color: rgb(99, 102, 241);
    }

    /* Animação para loading button */
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    .animate-spin {
        animation: spin 1s linear infinite;
    }

    /* Hover effects para tabela */
    tbody tr:hover {
        background-color: rgba(99, 102, 241, 0.02);
        transition: background-color 0.15s ease-in-out;
    }

    /* Estilo para placeholder dinâmico */
    input::placeholder {
        transition: opacity 0.3s ease-in-out;
    }

    /* Feedback visual para campos com valores */
    .has-value {
        background-color: rgba(99, 102, 241, 0.05);
        border-color: rgba(99, 102, 241, 0.3);
    }

    /* Enhanced table row animations */
    tbody tr {
        transition: all 0.2s ease-in-out;
    }

    tbody tr:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    /* Loading pulse animation */
    .loading-pulse {
        animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }

    @keyframes pulse {
        0%, 100% {
            opacity: 1;
        }
        50% {
            opacity: .5;
        }
    }

    /* Status button hover effects */
    button[id^="status-btn-"]:hover {
        transform: scale(1.05);
        transition: transform 0.15s ease-in-out;
    }

    /* Enhanced notification animations */
    .notification {
        animation: slideInRight 0.3s ease-out;
    }

    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
</style>

<script>
// Status toggle functionality
function toggleClientStatus(clientId, newStatus) {
    const statusBtn = document.getElementById(`status-btn-${clientId}`);
    const statusBadge = document.getElementById(`status-badge-${clientId}`);

    // Enhanced confirmation dialog
    const actionText = newStatus === 'active' ? 'ativar' : 'desativar';
    const actionIcon = newStatus === 'active' ? '✓' : '⚠';

    showConfirmDialog({
        title: `${actionIcon} Confirmar Ação`,
        message: `Tem certeza que deseja ${actionText} este cliente?`,
        confirmText: actionText.charAt(0).toUpperCase() + actionText.slice(1),
        cancelText: 'Cancelar',
        type: newStatus === 'active' ? 'success' : 'warning'
    }).then(confirmed => {
        if (!confirmed) return;

        processStatusChange();
    });

    function processStatusChange() {
        // Show enhanced loading state
        const loadingOverlay = showLoadingOverlay(`${actionText.charAt(0).toUpperCase() + actionText.slice(1)}ando cliente...`);

        statusBtn.disabled = true;
        statusBtn.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';

        // Get reason for status change
        let reason = '';
        if (newStatus === 'inactive') {
            reason = prompt('Motivo para desativar o cliente (opcional):') || '';
        }

        // Make AJAX request
        fetch(`/admin/relatorios/clientes/${clientId}/status`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                status: newStatus,
                reason: reason
            })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            hideLoadingOverlay();

            if (data.success) {
                // Update status badge
                const isActive = data.status === 'active';
                statusBadge.className = `px-2 py-1 font-semibold leading-tight rounded-full ${isActive ? 'text-green-700 bg-green-100' : 'text-red-700 bg-red-100'}`;
                statusBadge.innerHTML = isActive
                    ? '<svg class="w-3 h-3 inline mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>Ativo'
                    : '<svg class="w-3 h-3 inline mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>Inativo';

                // Update button
                const newButtonStatus = isActive ? 'inactive' : 'active';
                statusBtn.onclick = () => toggleClientStatus(clientId, newButtonStatus);
                statusBtn.className = `px-2 py-1 text-xs font-medium rounded focus:outline-none focus:ring-2 ${isActive ? 'text-red-600 bg-red-100 hover:bg-red-200 focus:ring-red-500' : 'text-green-600 bg-green-100 hover:bg-green-200 focus:ring-green-500'}`;
                statusBtn.title = isActive ? 'Desativar cliente' : 'Ativar cliente';
                statusBtn.innerHTML = isActive
                    ? '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728"></path></svg>'
                    : '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';

                // Show success message
                showNotification(data.message, 'success');
            } else {
                showNotification(data.message || 'Erro ao atualizar status do cliente', 'error');
            }
        })
        .catch(error => {
            hideLoadingOverlay();
            console.error('Error:', error);

            // Use the global error handler if available
            if (window.AdminErrorHandler) {
                window.AdminErrorHandler.showNotification('Erro de conexão ao atualizar status. Verifique sua conexão e tente novamente.', 'error');
            } else {
                showNotification('Erro de conexão. Tente novamente.', 'error');
            }
        })
        .finally(() => {
            statusBtn.disabled = false;
        });
    }
}

// Notification system
function showNotification(message, type = 'info') {
    // Remove existing notifications
    const existingNotifications = document.querySelectorAll('.notification');
    existingNotifications.forEach(notification => notification.remove());

    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg transition-all duration-300 transform translate-x-full ${
        type === 'success' ? 'bg-green-500 text-white' :
        type === 'error' ? 'bg-red-500 text-white' :
        'bg-blue-500 text-white'
    }`;
    notification.innerHTML = `
        <div class="flex items-center">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                ${type === 'success' ?
                    '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>' :
                    type === 'error' ?
                    '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>' :
                    '<path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>'
                }
            </svg>
            <span>${message}</span>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
            </button>
        </div>
    `;

    document.body.appendChild(notification);

    // Animate in
    setTimeout(() => {
        notification.classList.remove('translate-x-full');
    }, 100);

    // Auto remove after 5 seconds
    setTimeout(() => {
        notification.classList.add('translate-x-full');
        setTimeout(() => notification.remove(), 300);
    }, 5000);
}

// Enhanced confirmation dialog system
function showConfirmDialog(options) {
    const {
        title = 'Confirmar',
        message = 'Tem certeza?',
        confirmText = 'Confirmar',
        cancelText = 'Cancelar',
        type = 'info'
    } = options;

    return new Promise((resolve) => {
        // Create modal backdrop
        const backdrop = document.createElement('div');
        backdrop.className = 'fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4';
        backdrop.style.animation = 'fadeIn 0.2s ease-out';

        // Create modal
        const modal = document.createElement('div');
        modal.className = 'bg-white rounded-lg shadow-xl max-w-md w-full transform transition-all duration-200';
        modal.style.animation = 'slideIn 0.2s ease-out';

        const typeColors = {
            success: 'text-green-600 bg-green-100',
            warning: 'text-orange-600 bg-orange-100',
            error: 'text-red-600 bg-red-100',
            info: 'text-blue-600 bg-blue-100'
        };

        const typeIcons = {
            success: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>',
            warning: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16c-.77.833.192 2.5 1.732 2.5z"></path>',
            error: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>',
            info: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>'
        };

        modal.innerHTML = `
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="flex-shrink-0 w-10 h-10 rounded-full ${typeColors[type]} flex items-center justify-center mr-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            ${typeIcons[type]}
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900">${title}</h3>
                </div>
                <p class="text-sm text-gray-600 mb-6">${message}</p>
                <div class="flex justify-end space-x-3">
                    <button id="cancel-btn" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 transition-colors duration-150">
                        ${cancelText}
                    </button>
                    <button id="confirm-btn" class="px-4 py-2 text-sm font-medium text-white rounded-md focus:outline-none focus:ring-2 transition-colors duration-150 ${
                        type === 'success' ? 'bg-green-600 hover:bg-green-700 focus:ring-green-500' :
                        type === 'warning' ? 'bg-orange-600 hover:bg-orange-700 focus:ring-orange-500' :
                        type === 'error' ? 'bg-red-600 hover:bg-red-700 focus:ring-red-500' :
                        'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500'
                    }">
                        ${confirmText}
                    </button>
                </div>
            </div>
        `;

        backdrop.appendChild(modal);
        document.body.appendChild(backdrop);

        // Add event listeners
        const cancelBtn = modal.querySelector('#cancel-btn');
        const confirmBtn = modal.querySelector('#confirm-btn');

        function closeModal(result) {
            backdrop.style.animation = 'fadeOut 0.2s ease-out';
            modal.style.animation = 'slideOut 0.2s ease-out';
            setTimeout(() => {
                backdrop.remove();
                resolve(result);
            }, 200);
        }

        cancelBtn.addEventListener('click', () => closeModal(false));
        confirmBtn.addEventListener('click', () => closeModal(true));
        backdrop.addEventListener('click', (e) => {
            if (e.target === backdrop) closeModal(false);
        });

        // Focus on confirm button
        setTimeout(() => confirmBtn.focus(), 100);

        // Handle escape key
        const handleEscape = (e) => {
            if (e.key === 'Escape') {
                document.removeEventListener('keydown', handleEscape);
                closeModal(false);
            }
        };
        document.addEventListener('keydown', handleEscape);
    });
}

// Enhanced loading overlay system
function showLoadingOverlay(message = 'Carregando...') {
    // Remove existing overlay
    const existingOverlay = document.querySelector('.loading-overlay');
    if (existingOverlay) {
        existingOverlay.remove();
    }

    const overlay = document.createElement('div');
    overlay.className = 'loading-overlay fixed inset-0 bg-black bg-opacity-30 z-40 flex items-center justify-center';
    overlay.innerHTML = `
        <div class="bg-white rounded-lg shadow-xl p-6 flex items-center space-x-3">
            <svg class="animate-spin h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-gray-700 font-medium">${message}</span>
        </div>
    `;
    document.body.appendChild(overlay);
    return overlay;
}

function hideLoadingOverlay() {
    const overlay = document.querySelector('.loading-overlay');
    if (overlay) {
        overlay.remove();
    }
}

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    @keyframes fadeOut {
        from { opacity: 1; }
        to { opacity: 0; }
    }
    @keyframes slideIn {
        from { transform: scale(0.95) translateY(-10px); opacity: 0; }
        to { transform: scale(1) translateY(0); opacity: 1; }
    }
    @keyframes slideOut {
        from { transform: scale(1) translateY(0); opacity: 1; }
        to { transform: scale(0.95) translateY(-10px); opacity: 0; }
    }
    .tooltip-custom {
        z-index: 9999;
    }
`;
document.head.appendChild(style);

// Add CSRF token to meta tags if not present
if (!document.querySelector('meta[name="csrf-token"]')) {
    const meta = document.createElement('meta');
    meta.name = 'csrf-token';
    meta.content = '{{ csrf_token() }}';
    document.head.appendChild(meta);
}
</script>

</x-admin-layout>
