<x-admin-layout title="Detalhes do Cliente - {{ $client->name }}">
<div class="container px-6 mx-auto grid">
    <!-- Header com navegação -->
    <div class="flex justify-between items-center">
        <h2 class="my-6 text-2xl font-semibold text-gray-700">
            Detalhes do Cliente
        </h2>
        <div class="flex space-x-2">
            <a href="{{ route('admin.reports.clients.index') }}"
               class="px-4 py-2 text-sm text-indigo-600 bg-indigo-100 rounded-lg hover:bg-indigo-200 transition-colors duration-150">
                &larr; Voltar para Lista
            </a>
            <a href="{{ route('admin.reports.clients.export', ['search' => $client->email]) }}"
               class="px-4 py-2 text-sm text-green-600 bg-green-100 rounded-lg hover:bg-green-200 transition-colors duration-150">
                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Exportar Dados
            </a>
        </div>
    </div>

    <!-- Mensagens de sessão e erros de validação -->
    <x-admin.session-messages />
    <x-admin.validation-errors />

    <!-- Header do Cliente -->
    <div class="mb-8 p-6 bg-white rounded-lg shadow-md">
        <div class="flex items-center space-x-6">
            <!-- Avatar -->
            <x-admin.clients.avatar :client="$client" size="lg" />

            <!-- Informações básicas -->
            <div class="flex-1">
                <h3 class="text-2xl font-bold text-gray-900">{{ $client->name }}</h3>
                <p class="text-gray-600 text-lg">{{ $client->email }}</p>
                <div class="flex items-center space-x-4 mt-2">
                    <span class="text-sm text-gray-500">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        Cliente desde {{ $client->created_at->format('d/m/Y') }}
                    </span>
                    <span class="text-sm text-gray-500">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Último acesso: {{ $client->updated_at->diffForHumans() }}
                    </span>
                    <span class="text-sm text-gray-500">
                        ID: {{ $client->id }}
                    </span>
                </div>
            </div>

            <!-- Status badges -->
            <div class="flex flex-col space-y-2">
                <x-admin.status-badge
                    :type="$client->is_verified ? 'verified' : 'unverified'"
                    :text="$client->is_verified ? 'Email Verificado' : 'Email Pendente'"
                    size="sm" />

                <x-admin.status-badge
                    id="status-badge-detail"
                    :type="$client->status === 'active' ? 'active' : 'inactive'"
                    :text="$client->status === 'active' ? 'Conta Ativa' : 'Conta Inativa'"
                    size="sm" />

                <x-admin.status-badge
                    :type="$client->is_active ? 'recent-activity' : 'no-activity'"
                    :text="$client->is_active ? 'Ativo Recentemente' : 'Sem Atividade Recente'"
                    size="sm" />

                @if($clientStats['has_abandoned_cart'])
                    <x-admin.status-badge
                        type="abandoned-cart"
                        text="Carrinho Abandonado"
                        size="sm" />
                @endif
            </div>
        </div>
    </div>

    <!-- Status Management Section -->
    <div class="mb-8 p-6 bg-white rounded-lg shadow-md border-l-4 {{ $client->status === 'active' ? 'border-green-500' : 'border-red-500' }}">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-700 mb-2 flex items-center">
                    <svg class="w-5 h-5 mr-2 {{ $client->status === 'active' ? 'text-green-500' : 'text-red-500' }}" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    Gestão de Status da Conta
                </h3>
                <div class="space-y-2">
                    <p class="text-sm text-gray-600">
                        <span class="font-medium">Status atual:</span>
                        <span id="current-status-text" class="{{ $client->status === 'active' ? 'text-green-600' : 'text-red-600' }} font-semibold">
                            {{ $client->status_label }}
                        </span>
                    </p>
                    @if($client->status_updated_at)
                        <p class="text-sm text-gray-600">
                            <span class="font-medium">Última alteração:</span>
                            {{ $client->status_updated_at->format('d/m/Y H:i') }}
                        </p>
                    @endif
                    @if($client->status_reason)
                        <p class="text-sm text-gray-600">
                            <span class="font-medium">Motivo:</span>
                            <span class="italic">{{ $client->status_reason }}</span>
                        </p>
                    @endif
                </div>
            </div>

            <div class="flex items-center space-x-3">
                <button onclick="toggleClientStatusDetail('{{ $client->id }}', '{{ $client->status === 'active' ? 'inactive' : 'active' }}')"
                        id="status-btn-detail"
                        class="px-4 py-2 text-sm font-medium rounded-lg focus:outline-none focus:ring-2 transition-colors duration-200 {{ $client->status === 'active' ? 'text-red-600 bg-red-100 hover:bg-red-200 focus:ring-red-500' : 'text-green-600 bg-green-100 hover:bg-green-200 focus:ring-green-500' }}">
                    @if($client->status === 'active')
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728"></path>
                        </svg>
                        Desativar Conta
                    @else
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Ativar Conta
                    @endif
                </button>

                @if($client->status === 'inactive')
                    <div class="px-3 py-2 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <div class="flex items-center">
                            <svg class="w-4 h-4 text-yellow-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-sm text-yellow-700 font-medium">Cliente não pode fazer login</span>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Cards de Estatísticas do Cliente -->
    <div class="grid gap-6 mb-8 md:grid-cols-2 xl:grid-cols-4">
        <x-admin.stats-card
            title="Total de Pedidos"
            :value="$clientStats['total_orders']"
            icon-color="blue"
            :icon="'<path stroke-linecap=&quot;round&quot; stroke-linejoin=&quot;round&quot; stroke-width=&quot;2&quot; d=&quot;M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z&quot;></path>'" />

        <x-admin.stats-card
            title="Valor Total Gasto"
            :value="'R$ ' . number_format($clientStats['total_spent'], 2, ',', '.')"
            icon-color="green"
            :icon="'<path stroke-linecap=&quot;round&quot; stroke-linejoin=&quot;round&quot; stroke-width=&quot;2&quot; d=&quot;M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1&quot;></path>'" />

        <x-admin.stats-card
            title="Ticket Médio"
            :value="'R$ ' . number_format($clientStats['average_order_value'], 2, ',', '.')"
            icon-color="purple"
            :icon="'<path stroke-linecap=&quot;round&quot; stroke-linejoin=&quot;round&quot; stroke-width=&quot;2&quot; d=&quot;M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z&quot;></path>'" />

        <x-admin.stats-card
            title="Dias como Cliente"
            :value="$clientStats['registration_days']"
            icon-color="indigo"
            :icon="'<path stroke-linecap=&quot;round&quot; stroke-linejoin=&quot;round&quot; stroke-width=&quot;2&quot; d=&quot;M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z&quot;></path>'" />
    </div>

    <!-- Grid Principal: Informações e Endereços -->
    <div class="grid gap-6 mb-8 md:grid-cols-2">
        <!-- Informações Pessoais -->
        <div class="p-6 bg-white rounded-lg shadow-md">
            <h3 class="text-lg font-semibold text-gray-700 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                Informações Pessoais
            </h3>

            <div class="space-y-4">
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-sm font-medium text-gray-600">Nome Completo:</span>
                    <span class="text-sm text-gray-900">{{ $client->name }}</span>
                </div>

                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-sm font-medium text-gray-600">Email:</span>
                    <span class="text-sm text-gray-900">{{ $client->email }}</span>
                </div>

                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-sm font-medium text-gray-600">Data de Cadastro:</span>
                    <span class="text-sm text-gray-900">{{ $client->created_at->format('d/m/Y H:i') }}</span>
                </div>

                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-sm font-medium text-gray-600">Último Acesso:</span>
                    <span class="text-sm text-gray-900">{{ $client->updated_at->format('d/m/Y H:i') }}</span>
                </div>

                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-sm font-medium text-gray-600">Status do Email:</span>
                    <span class="text-sm">
                        @if($client->is_verified)
                            <span class="px-2 py-1 text-xs font-medium text-green-700 bg-green-100 rounded-full">
                                Verificado em {{ $client->email_verified_at->format('d/m/Y') }}
                            </span>
                        @else
                            <span class="px-2 py-1 text-xs font-medium text-yellow-700 bg-yellow-100 rounded-full">
                                Não Verificado
                            </span>
                        @endif
                    </span>
                </div>

                @if($clientStats['last_order_date'])
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-sm font-medium text-gray-600">Último Pedido:</span>
                    <span class="text-sm text-gray-900">{{ $clientStats['last_order_date']->format('d/m/Y') }}</span>
                </div>
                @endif
            </div>
        </div>

        <!-- Endereços -->
        <div class="p-6 bg-white rounded-lg shadow-md">
            <h3 class="text-lg font-semibold text-gray-700 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                Endereços Cadastrados
                <span class="ml-2 px-2 py-1 text-xs font-medium text-indigo-700 bg-indigo-100 rounded-full">
                    {{ $client->addresses->count() }}
                </span>
            </h3>

            @if($client->addresses->count() > 0)
                <div class="space-y-4">
                    @foreach($client->addresses as $address)
                        <x-admin.clients.address-display :address="$address" />
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <p class="text-gray-500 text-sm">Nenhum endereço cadastrado</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Seção de Atividade do Cliente -->
    <div class="grid gap-6 mb-8 md:grid-cols-3">
        <!-- Itens na Wishlist -->
        <div class="p-6 bg-white rounded-lg shadow-md">
            <h3 class="text-lg font-semibold text-gray-700 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path>
                </svg>
                Lista de Desejos
                <span class="ml-2 px-2 py-1 text-xs font-medium text-red-700 bg-red-100 rounded-full">
                    {{ $clientStats['wishlist_items'] }}
                </span>
            </h3>

            @if($client->wishlists->count() > 0)
                <div class="grid grid-cols-2 gap-3 max-h-80 overflow-y-auto">
                    @foreach($client->wishlists->take(8) as $wishlistItem)
                        @if($wishlistItem->product && $wishlistItem->product->productable)
                            @php
                                $vinyl = $wishlistItem->product->productable;
                                $imageUrl = vinyl_image_url($vinyl->cover_image);
                                $artists = $vinyl->artists->pluck('name')->join(', ');
                            @endphp
                            <div class="group relative bg-white border border-gray-200 rounded-lg p-3 hover:shadow-md transition-shadow duration-200">
                                <!-- Imagem do disco -->
                                <div class="aspect-square mb-2 overflow-hidden rounded-lg bg-gray-100">
                                    <img src="{{ $imageUrl }}"
                                         alt="{{ $vinyl->title }}"
                                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200"
                                         loading="lazy"
                                         onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjQiIGhlaWdodD0iNjQiIHZpZXdCb3g9IjAgMCA2NCA2NCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjY0IiBoZWlnaHQ9IjY0IiBmaWxsPSIjRjNGNEY2Ii8+CjxwYXRoIGQ9Ik0yMCAyMEg0NFY0NEgyMFYyMFoiIHN0cm9rZT0iIzlDQTNBRiIgc3Ryb2tlLXdpZHRoPSIyIiBmaWxsPSJub25lIi8+CjxwYXRoIGQ9Ik0yOCAzMkwzMiAyOEwzNiAzMkwzMiAzNkwyOCAzMloiIGZpbGw9IiM5Q0EzQUYiLz4KPC9zdmc+'">
                                </div>

                                <!-- Informações do disco -->
                                <div class="space-y-1">
                                    <h4 class="text-sm font-medium text-gray-900 line-clamp-2 leading-tight">
                                        {{ $vinyl->title }}
                                    </h4>
                                    @if($artists)
                                        <p class="text-xs text-gray-600 line-clamp-1">
                                            {{ $artists }}
                                        </p>
                                    @endif
                                    <div class="flex items-center justify-between">
                                        <p class="text-xs text-gray-500">
                                            {{ $wishlistItem->created_at->format('d/m/Y') }}
                                        </p>
                                        @if($vinyl->vinylSec && $vinyl->vinylSec->price)
                                            <p class="text-xs font-medium text-green-600">
                                                R$ {{ number_format($vinyl->vinylSec->price, 2, ',', '.') }}
                                            </p>
                                        @endif
                                    </div>
                                </div>

                                <!-- Badge de disponibilidade -->
                                @if($vinyl->vinylSec && $vinyl->vinylSec->in_stock && $vinyl->vinylSec->stock > 0)
                                    <div class="absolute top-2 right-2">
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <svg class="w-2 h-2 mr-1" fill="currentColor" viewBox="0 0 8 8">
                                                <circle cx="4" cy="4" r="3"/>
                                            </svg>
                                            Disponível
                                        </span>
                                    </div>
                                @else
                                    <div class="absolute top-2 right-2">
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <svg class="w-2 h-2 mr-1" fill="currentColor" viewBox="0 0 8 8">
                                                <circle cx="4" cy="4" r="3"/>
                                            </svg>
                                            Esgotado
                                        </span>
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="bg-gray-50 border border-gray-200 rounded-lg p-3 flex items-center justify-center">
                                <div class="text-center">
                                    <svg class="w-8 h-8 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                    <p class="text-xs text-gray-500">Produto não encontrado</p>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>

                @if($client->wishlists->count() > 8)
                    <div class="mt-4 pt-4 border-t border-gray-200 text-center">
                        <p class="text-xs text-gray-500">
                            E mais {{ $client->wishlists->count() - 8 }} itens na lista de desejos
                        </p>
                    </div>
                @endif
            @else
                <div class="text-center py-8">
                    <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                    </svg>
                    <p class="text-gray-500 text-sm">Nenhum item na lista de desejos</p>
                </div>
            @endif
        </div>

        <!-- Itens no Carrinho -->
        <div class="p-6 bg-white rounded-lg shadow-md {{ $clientStats['has_abandoned_cart'] ? 'ring-2 ring-orange-200' : '' }}">
            <h3 class="text-lg font-semibold text-gray-700 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m6-5v6a2 2 0 01-2 2H9a2 2 0 01-2-2v-6m8 0V9a2 2 0 00-2-2H9a2 2 0 00-2 2v4.01"></path>
                </svg>
                Carrinho de Compras
                <span class="ml-2 px-2 py-1 text-xs font-medium text-green-700 bg-green-100 rounded-full">
                    {{ $clientStats['cart_items'] }}
                </span>
                @if($clientStats['has_abandoned_cart'])
                    <span class="ml-1 px-2 py-1 text-xs font-medium text-orange-700 bg-orange-100 rounded-full">
                        <svg class="w-3 h-3 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                        Abandonado
                    </span>
                @endif
            </h3>

            @if($client->cart && $client->cart->products->count() > 0)
                @php
                    $isAbandoned = $clientStats['has_abandoned_cart'];
                    $abandonedDays = $isAbandoned ? $client->cart->updated_at->diffInDays(now()) : 0;
                @endphp

                @if($isAbandoned)
                    <div class="mb-4 p-3 bg-orange-50 border border-orange-200 rounded-lg">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-orange-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-orange-800">Carrinho Abandonado</p>
                                <p class="text-xs text-orange-700">
                                    Última atualização há {{ $abandonedDays }} {{ $abandonedDays === 1 ? 'dia' : 'dias' }}
                                    ({{ $client->cart->updated_at->format('d/m/Y H:i') }})
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="space-y-3 max-h-80 overflow-y-auto">
                    @foreach($client->cart->products->take(6) as $cartProduct)
                        @php
                            $vinyl = $cartProduct->productable;
                            $imageUrl = $vinyl ? vinyl_image_url($vinyl->cover_image) : null;
                            $artists = $vinyl && $vinyl->artists ? $vinyl->artists->pluck('name')->join(', ') : '';
                            $itemTotal = $cartProduct->price * $cartProduct->pivot->quantity;
                        @endphp

                        <div class="flex items-center space-x-3 p-3 border rounded-lg transition-colors duration-200 {{ $isAbandoned ? 'bg-orange-50 border-orange-200 hover:bg-orange-100' : 'border-gray-200 hover:bg-gray-50' }}">
                            <!-- Imagem do produto -->
                            <div class="flex-shrink-0">
                                @if($imageUrl)
                                    <img src="{{ $imageUrl }}"
                                         alt="{{ $cartProduct->name }}"
                                         class="w-16 h-16 object-cover rounded-lg {{ $isAbandoned ? 'ring-2 ring-orange-300' : '' }}"
                                         loading="lazy"
                                         onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjQiIGhlaWdodD0iNjQiIHZpZXdCb3g9IjAgMCA2NCA2NCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjY0IiBoZWlnaHQ9IjY0IiBmaWxsPSIjRjNGNEY2Ii8+CjxwYXRoIGQ9Ik0yMCAyMEg0NFY0NEgyMFYyMFoiIHN0cm9rZT0iIzlDQTNBRiIgc3Ryb2tlLXdpZHRoPSIyIiBmaWxsPSJub25lIi8+CjxwYXRoIGQ9Ik0yOCAzMkwzMiAyOEwzNiAzMkwzMiAzNkwyOCAzMloiIGZpbGw9IiM5Q0EzQUYiLz4KPC9zdmc+'">
                                @else
                                    <div class="w-16 h-16 bg-gray-200 rounded-lg flex items-center justify-center {{ $isAbandoned ? 'ring-2 ring-orange-300' : '' }}">
                                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"></path>
                                        </svg>
                                    </div>
                                @endif
                            </div>

                            <!-- Informações do produto -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1 min-w-0">
                                        <h4 class="text-sm font-medium text-gray-900 line-clamp-2">
                                            {{ $cartProduct->name }}
                                        </h4>
                                        @if($artists)
                                            <p class="text-xs text-gray-600 mt-1 line-clamp-1">
                                                {{ $artists }}
                                            </p>
                                        @endif

                                        <!-- Informações de quantidade e preço -->
                                        <div class="flex items-center justify-between mt-2">
                                            <div class="flex items-center space-x-3">
                                                <span class="text-xs text-gray-500">
                                                    Qtd: <span class="font-medium">{{ $cartProduct->pivot->quantity }}</span>
                                                </span>
                                                <span class="text-xs text-gray-500">
                                                    Unit: <span class="font-medium">R$ {{ number_format($cartProduct->price, 2, ',', '.') }}</span>
                                                </span>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-sm font-bold {{ $isAbandoned ? 'text-orange-700' : 'text-gray-900' }}">
                                                    R$ {{ number_format($itemTotal, 2, ',', '.') }}
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Data de adição ao carrinho -->
                                        @if($cartProduct->pivot->created_at)
                                            <p class="text-xs text-gray-500 mt-1">
                                                Adicionado em {{ \Carbon\Carbon::parse($cartProduct->pivot->created_at)->format('d/m/Y H:i') }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Badge de item abandonado -->
                            @if($isAbandoned)
                                <div class="flex-shrink-0">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                        </svg>
                                        {{ $abandonedDays }}d
                                    </span>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                @if($client->cart->products->count() > 6)
                    <div class="mt-4 pt-4 border-t border-gray-200 text-center">
                        <p class="text-xs text-gray-500">
                            E mais {{ $client->cart->products->count() - 6 }} itens no carrinho
                        </p>
                    </div>
                @endif

                <!-- Total do carrinho -->
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-600">Total do Carrinho:</span>
                        <span class="text-lg font-bold {{ $isAbandoned ? 'text-orange-700' : 'text-gray-900' }}">
                            R$ {{ number_format($clientStats['cart_total'], 2, ',', '.') }}
                        </span>
                    </div>

                    @if($isAbandoned)
                        <div class="mt-2 p-2 bg-orange-50 rounded-lg">
                            <p class="text-xs text-orange-700 flex items-center">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                </svg>
                                <strong>Carrinho abandonado há {{ $abandonedDays }} {{ $abandonedDays === 1 ? 'dia' : 'dias' }}</strong>
                                - Oportunidade de recuperação de vendas
                            </p>
                        </div>
                    @endif
                </div>
            @else
                <div class="text-center py-8">
                    <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m6-5v6a2 2 0 01-2 2H9a2 2 0 01-2-2v-6m8 0V9a2 2 0 00-2-2H9a2 2 0 00-2 2v4.01"></path>
                    </svg>
                    <p class="text-gray-500 text-sm">Carrinho vazio</p>
                </div>
            @endif
        </div>

        <!-- Resumo de Atividade -->
        <div class="p-6 bg-white rounded-lg shadow-md">
            <h3 class="text-lg font-semibold text-gray-700 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                Resumo de Atividade
            </h3>

            <div class="space-y-4">
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-sm text-gray-600">Pedidos Realizados:</span>
                    <span class="text-sm font-medium text-gray-900">{{ $clientStats['total_orders'] }}</span>
                </div>

                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-sm text-gray-600">Itens Favoritados:</span>
                    <span class="text-sm font-medium text-gray-900">{{ $clientStats['wishlist_items'] }}</span>
                </div>

                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-sm text-gray-600">Itens no Carrinho:</span>
                    <span class="text-sm font-medium text-gray-900">{{ $clientStats['cart_items'] }}</span>
                </div>

                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-sm text-gray-600">Endereços Cadastrados:</span>
                    <span class="text-sm font-medium text-gray-900">{{ $client->addresses->count() }}</span>
                </div>

                @if($clientStats['last_order_date'])
                <div class="flex justify-between items-center py-2">
                    <span class="text-sm text-gray-600">Último Pedido:</span>
                    <span class="text-sm font-medium text-gray-900">{{ $clientStats['last_order_date']->diffForHumans() }}</span>
                </div>
                @else
                <div class="flex justify-between items-center py-2">
                    <span class="text-sm text-gray-600">Último Pedido:</span>
                    <span class="text-sm text-gray-500 italic">Nenhum pedido</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Seção de Pedidos do Cliente -->
    <div class="mb-8">
        <div class="p-6 bg-white rounded-lg shadow-md">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-semibold text-gray-700 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                    Histórico de Pedidos
                    <span class="ml-2 px-2 py-1 text-xs font-medium text-blue-700 bg-blue-100 rounded-full">
                        {{ $clientStats['total_orders'] }}
                    </span>
                </h3>

                @if($clientStats['total_orders'] > 0)
                    <div class="text-sm text-gray-600">
                        <span class="font-medium">Total gasto:</span>
                        <span class="text-green-600 font-bold">R$ {{ number_format($clientStats['total_spent'], 2, ',', '.') }}</span>
                    </div>
                @endif
            </div>

            @if($client->orders->count() > 0)
                <!-- Estatísticas de Compras -->
                <div class="grid gap-4 mb-6 md:grid-cols-3">
                    <div class="p-4 bg-blue-50 rounded-lg border border-blue-200">
                        <div class="flex items-center">
                            <div class="p-2 bg-blue-100 rounded-lg">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-blue-600">Total de Pedidos</p>
                                <p class="text-lg font-bold text-blue-900">{{ $clientStats['total_orders'] }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-4 bg-green-50 rounded-lg border border-green-200">
                        <div class="flex items-center">
                            <div class="p-2 bg-green-100 rounded-lg">
                                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-green-600">Valor Total</p>
                                <p class="text-lg font-bold text-green-900">R$ {{ number_format($clientStats['total_spent'], 2, ',', '.') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-4 bg-purple-50 rounded-lg border border-purple-200">
                        <div class="flex items-center">
                            <div class="p-2 bg-purple-100 rounded-lg">
                                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-purple-600">Ticket Médio</p>
                                <p class="text-lg font-bold text-purple-900">R$ {{ number_format($clientStats['average_order_value'], 2, ',', '.') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabela de Pedidos -->
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr>
                                <th scope="col" class="px-4 py-3">Número do Pedido</th>
                                <th scope="col" class="px-4 py-3">Data</th>
                                <th scope="col" class="px-4 py-3">Status</th>
                                <th scope="col" class="px-4 py-3">Pagamento</th>
                                <th scope="col" class="px-4 py-3">Valor Total</th>
                                <th scope="col" class="px-4 py-3">Itens</th>
                                <th scope="col" class="px-4 py-3">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($client->orders as $order)
                                <tr class="bg-white border-b hover:bg-gray-50">
                                    <td class="px-4 py-4">
                                        <div class="font-medium text-gray-900">{{ $order->order_number }}</div>
                                        @if($order->tracking_code)
                                            <div class="text-xs text-gray-500">
                                                Rastreio: {{ $order->tracking_code }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="text-gray-900">{{ $order->created_at->format('d/m/Y') }}</div>
                                        <div class="text-xs text-gray-500">{{ $order->created_at->format('H:i') }}</div>
                                    </td>
                                    <td class="px-4 py-4">
                                        <span class="px-2 py-1 text-xs font-medium rounded-full {{ $order->getStatusBadgeClass() }}">
                                            {{ $order->getStatusLabel() }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4">
                                        <span class="px-2 py-1 text-xs font-medium rounded-full
                                            @if($order->payment_status === 'approved') bg-green-100 text-green-800
                                            @elseif($order->payment_status === 'pending') bg-yellow-100 text-yellow-800
                                            @elseif($order->payment_status === 'rejected') bg-red-100 text-red-800
                                            @else bg-gray-100 text-gray-800 @endif">
                                            {{ $order->getPaymentStatusLabel() }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="font-medium text-gray-900">
                                            R$ {{ number_format($order->total, 2, ',', '.') }}
                                        </div>
                                        @if($order->discount > 0)
                                            <div class="text-xs text-green-600">
                                                Desconto: R$ {{ number_format($order->discount, 2, ',', '.') }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="text-gray-900">{{ $order->items->count() }} {{ $order->items->count() === 1 ? 'item' : 'itens' }}</div>
                                        @if($order->items->count() > 0)
                                            <div class="text-xs text-gray-500">
                                                {{ $order->items->sum('quantity') }} {{ $order->items->sum('quantity') === 1 ? 'unidade' : 'unidades' }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="flex items-center space-x-2">
                                            <a href="{{ route('admin.orders.show', $order->id) }}"
                                               class="inline-flex items-center px-3 py-1 text-xs font-medium text-blue-600 bg-blue-100 rounded-lg hover:bg-blue-200 transition-colors duration-150"
                                               title="Ver detalhes do pedido">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                                Ver Detalhes
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($clientStats['total_orders'] > $client->orders->count())
                    <div class="mt-4 text-center">
                        <p class="text-sm text-gray-600 mb-3">
                            Mostrando {{ $client->orders->count() }} de {{ $clientStats['total_orders'] }} pedidos
                        </p>
                        <a href="{{ route('admin.orders.index', ['search' => $client->email]) }}"
                           class="inline-flex items-center px-4 py-2 text-sm font-medium text-blue-600 bg-blue-100 rounded-lg hover:bg-blue-200 transition-colors duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                            </svg>
                            Ver Todos os Pedidos
                        </a>
                    </div>
                @endif

            @else
                <!-- Estado vazio - Cliente sem pedidos -->
                <div class="text-center py-12">
                    <div class="mx-auto w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                    </div>
                    <h4 class="text-lg font-medium text-gray-900 mb-2">Nenhum pedido realizado</h4>
                    <p class="text-gray-600 mb-6">
                        Este cliente ainda não realizou nenhum pedido em nossa loja.
                    </p>

                    <!-- Informações úteis para clientes sem pedidos -->
                    <div class="max-w-md mx-auto">
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <h5 class="text-sm font-medium text-blue-900 mb-2">Informações do Cliente</h5>
                            <div class="space-y-2 text-sm text-blue-800">
                                <div class="flex justify-between">
                                    <span>Cadastrado há:</span>
                                    <span class="font-medium">{{ $clientStats['registration_days'] }} dias</span>
                                </div>
                                @if($clientStats['wishlist_items'] > 0)
                                    <div class="flex justify-between">
                                        <span>Itens favoritados:</span>
                                        <span class="font-medium">{{ $clientStats['wishlist_items'] }}</span>
                                    </div>
                                @endif
                                @if($clientStats['cart_items'] > 0)
                                    <div class="flex justify-between">
                                        <span>Itens no carrinho:</span>
                                        <span class="font-medium">{{ $clientStats['cart_items'] }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        @if($clientStats['cart_items'] > 0)
                            <div class="mt-4 p-4 bg-orange-50 border border-orange-200 rounded-lg">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-orange-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                    <div>
                                        <p class="text-sm font-medium text-orange-800">Cliente com carrinho ativo</p>
                                        <p class="text-xs text-orange-700">
                                            Valor total: R$ {{ number_format($clientStats['cart_total'], 2, ',', '.') }}
                                            @if($clientStats['has_abandoned_cart'])
                                                - Carrinho abandonado há mais de 7 dias
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
// Status toggle functionality for detail page
function toggleClientStatusDetail(clientId, newStatus) {
    const statusBtn = document.getElementById('status-btn-detail');
    const statusBadge = document.getElementById('status-badge-detail');
    const currentStatusText = document.getElementById('current-status-text');

    // Enhanced confirmation dialog
    const actionText = newStatus === 'active' ? 'ativar' : 'desativar';
    const actionIcon = newStatus === 'active' ? '✓' : '⚠';

    showConfirmDialogDetail({
        title: `${actionIcon} Confirmar Ação`,
        message: `Tem certeza que deseja ${actionText} este cliente?`,
        confirmText: actionText.charAt(0).toUpperCase() + actionText.slice(1),
        cancelText: 'Cancelar',
        type: newStatus === 'active' ? 'success' : 'warning'
    }).then(confirmed => {
        if (!confirmed) return;

        processStatusChangeDetail();
    });

    function processStatusChangeDetail() {
        // Show enhanced loading state
        const loadingOverlay = showLoadingOverlayDetail(`${actionText.charAt(0).toUpperCase() + actionText.slice(1)}ando cliente...`);

        statusBtn.disabled = true;
        statusBtn.innerHTML = '<svg class="w-4 h-4 animate-spin inline mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Processando...';

        // Get reason for status change
        let reason = '';
        if (newStatus === 'inactive') {
            reason = prompt('Motivo para desativar o cliente (opcional):') || '';
        }

    // Show loading state
    statusBtn.disabled = true;
    statusBtn.innerHTML = '<svg class="w-4 h-4 animate-spin inline mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Processando...';

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
            hideLoadingOverlayDetail();

            if (data.success) {
            // Update status badge
            const isActive = data.status === 'active';
            statusBadge.className = `px-3 py-1 text-sm font-medium rounded-full ${isActive ? 'text-green-700 bg-green-100' : 'text-red-700 bg-red-100'}`;
            statusBadge.innerHTML = isActive
                ? '<svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>Conta Ativa'
                : '<svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>Conta Inativa';

            // Update current status text
            currentStatusText.className = isActive ? 'text-green-600 font-semibold' : 'text-red-600 font-semibold';
            currentStatusText.textContent = data.status_label;

            // Update button
            const newButtonStatus = isActive ? 'inactive' : 'active';
            statusBtn.onclick = () => toggleClientStatusDetail(clientId, newButtonStatus);
            statusBtn.className = `px-4 py-2 text-sm font-medium rounded-lg focus:outline-none focus:ring-2 transition-colors duration-200 ${isActive ? 'text-red-600 bg-red-100 hover:bg-red-200 focus:ring-red-500' : 'text-green-600 bg-green-100 hover:bg-green-200 focus:ring-green-500'}`;
            statusBtn.innerHTML = isActive
                ? '<svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728"></path></svg>Desativar Conta'
                : '<svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>Ativar Conta';

            // Update the management section border color
            const managementSection = statusBtn.closest('.border-l-4');
            if (managementSection) {
                managementSection.className = managementSection.className.replace(
                    isActive ? 'border-red-500' : 'border-green-500',
                    isActive ? 'border-green-500' : 'border-red-500'
                );
            }

            // Show success message
            showNotificationDetail(data.message, 'success');

            // Reload page after 2 seconds to show updated information
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        } else {
            showNotificationDetail(data.message || 'Erro ao atualizar status do cliente', 'error');
        }
    })
        .catch(error => {
            hideLoadingOverlayDetail();
            console.error('Error:', error);

            // Use the global error handler if available
            if (window.AdminErrorHandler) {
                window.AdminErrorHandler.showNotification('Erro de conexão ao atualizar status. Verifique sua conexão e tente novamente.', 'error');
            } else {
                showNotificationDetail('Erro de conexão. Tente novamente.', 'error');
            }
        })
        .finally(() => {
            statusBtn.disabled = false;
        });
    }
}

// Notification system for detail page
function showNotificationDetail(message, type = 'info') {
    // Remove existing notifications
    const existingNotifications = document.querySelectorAll('.notification-detail');
    existingNotifications.forEach(notification => notification.remove());

    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification-detail fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg transition-all duration-300 transform translate-x-full ${
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

// Enhanced confirmation dialog system for detail page
function showConfirmDialogDetail(options) {
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
                    <button id="cancel-btn-detail" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 transition-colors duration-150">
                        ${cancelText}
                    </button>
                    <button id="confirm-btn-detail" class="px-4 py-2 text-sm font-medium text-white rounded-md focus:outline-none focus:ring-2 transition-colors duration-150 ${
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
        const cancelBtn = modal.querySelector('#cancel-btn-detail');
        const confirmBtn = modal.querySelector('#confirm-btn-detail');

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

// Enhanced loading overlay system for detail page
function showLoadingOverlayDetail(message = 'Carregando...') {
    // Remove existing overlay
    const existingOverlay = document.querySelector('.loading-overlay-detail');
    if (existingOverlay) {
        existingOverlay.remove();
    }

    const overlay = document.createElement('div');
    overlay.className = 'loading-overlay-detail fixed inset-0 bg-black bg-opacity-30 z-40 flex items-center justify-center';
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

function hideLoadingOverlayDetail() {
    const overlay = document.querySelector('.loading-overlay-detail');
    if (overlay) {
        overlay.remove();
    }
}

// Tooltip system for detail page
function initTooltipsDetail() {
    // Add tooltips to various elements
    const tooltipElements = [
        { selector: 'a[href*="export"]', text: 'Exportar dados específicos deste cliente para arquivo CSV' },
        { selector: '#status-btn-detail', text: 'Alterar status da conta do cliente (ativo/inativo)' },
        { selector: '.stats-card', text: 'Estatísticas detalhadas do comportamento do cliente' }
    ];

    tooltipElements.forEach(function(tooltipData) {
        const elements = document.querySelectorAll(tooltipData.selector);
        elements.forEach(element => {
            if (element) {
                createTooltipDetail(element, tooltipData.text);
            }
        });
    });
}

function createTooltipDetail(element, text) {
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
            tooltip.className = 'tooltip-custom-detail fixed z-50 px-3 py-2 text-sm text-white bg-gray-900 rounded-lg shadow-lg pointer-events-none transition-opacity duration-200 opacity-0';
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

// Add CSS animations for detail page
const styleDetail = document.createElement('style');
styleDetail.textContent = `
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
    .tooltip-custom-detail {
        z-index: 9999;
    }
`;
document.head.appendChild(styleDetail);

// Initialize tooltips and keyboard shortcuts when page loads
document.addEventListener('DOMContentLoaded', function() {
    initTooltipsDetail();
    initKeyboardShortcuts();
});

// Keyboard shortcuts for detail page
function initKeyboardShortcuts() {
    document.addEventListener('keydown', function(e) {
        // Alt + S para alternar status
        if (e.altKey && e.key === 's') {
            e.preventDefault();
            const statusBtn = document.getElementById('status-btn-detail');
            if (statusBtn && !statusBtn.disabled) {
                statusBtn.click();
            }
        }

        // Alt + B para voltar à lista
        if (e.altKey && e.key === 'b') {
            e.preventDefault();
            const backBtn = document.querySelector('a[href*="admin.reports.clients.index"]');
            if (backBtn) {
                window.location.href = backBtn.href;
            }
        }

        // Alt + E para exportar
        if (e.altKey && e.key === 'e') {
            e.preventDefault();
            const exportBtn = document.querySelector('a[href*="export"]');
            if (exportBtn) {
                window.location.href = exportBtn.href;
            }
        }
    });

    // Add visual indicators for keyboard shortcuts
    const statusBtn = document.getElementById('status-btn-detail');
    if (statusBtn) {
        statusBtn.title = statusBtn.title + ' (Alt+S)';
    }

    const backBtn = document.querySelector('a[href*="admin.reports.clients.index"]');
    if (backBtn) {
        backBtn.title = 'Voltar para lista de clientes (Alt+B)';
    }

    const exportBtn = document.querySelector('a[href*="export"]');
    if (exportBtn) {
        exportBtn.title = exportBtn.title + ' (Alt+E)';
    }
}

// Add CSRF token to meta tags if not present
if (!document.querySelector('meta[name="csrf-token"]')) {
    const meta = document.createElement('meta');
    meta.name = 'csrf-token';
    meta.content = '{{ csrf_token() }}';
    document.head.appendChild(meta);
}
</script>

</x-admin-layout>
