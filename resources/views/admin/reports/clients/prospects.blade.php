<x-admin-layout title="Prospects de Alto Valor">
<div class="container px-6 mx-auto grid">
    <!-- Header -->
    <div class="flex justify-between items-center my-6">
        <h2 class="text-2xl font-semibold text-gray-700">
            Prospects de Alto Valor
        </h2>
        <div class="flex space-x-2">
            <a href="{{ route('admin.reports.clients.index') }}"
               class="px-4 py-2 text-sm text-indigo-600 bg-indigo-100 rounded-lg hover:bg-indigo-200 transition-colors duration-150">
                &larr; Voltar para Clientes
            </a>
        </div>
    </div>

    <!-- Mensagens de sessão -->
    <x-admin.session-messages />

    <!-- Estatísticas Resumidas -->
    <div class="grid gap-6 mb-8 md:grid-cols-4">
        <div class="flex items-center p-4 bg-white rounded-lg shadow-xs">
            <div class="p-3 mr-4 text-orange-500 bg-orange-100 rounded-full">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"></path>
                </svg>
            </div>
            <div>
                <p class="mb-2 text-sm font-medium text-gray-600">Carrinhos Alto Valor</p>
                <p class="text-lg font-semibold text-gray-700">{{ $stats['high_value_carts_count'] }}</p>
            </div>
        </div>

        <div class="flex items-center p-4 bg-white rounded-lg shadow-xs">
            <div class="p-3 mr-4 text-green-500 bg-green-100 rounded-full">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"></path>
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div>
                <p class="mb-2 text-sm font-medium text-gray-600">Valor Total</p>
                <p class="text-lg font-semibold text-gray-700">R$ {{ number_format($stats['high_value_total'], 2, ',', '.') }}</p>
            </div>
        </div>

        <div class="flex items-center p-4 bg-white rounded-lg shadow-xs">
            <div class="p-3 mr-4 text-blue-500 bg-blue-100 rounded-full">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                </svg>
            </div>
            <div>
                <p class="mb-2 text-sm font-medium text-gray-600">Wishlist Prospects</p>
                <p class="text-lg font-semibold text-gray-700">{{ $stats['wishlist_prospects_count'] }}</p>
            </div>
        </div>

        <div class="flex items-center p-4 bg-white rounded-lg shadow-xs">
            <div class="p-3 mr-4 text-purple-500 bg-purple-100 rounded-full">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div>
                <p class="mb-2 text-sm font-medium text-gray-600">Visitantes Recentes</p>
                <p class="text-lg font-semibold text-gray-700">{{ $stats['recent_visitors_count'] }}</p>
            </div>
        </div>
    </div>

    <!-- Carrinhos de Alto Valor -->
    @if($highValueCarts->count() > 0)
    <div class="w-full overflow-hidden rounded-lg shadow-xs mb-8">
        <div class="w-full overflow-x-auto">
            <div class="px-4 py-3 bg-gray-50 border-b">
                <h3 class="text-lg font-semibold text-gray-700">
                    🛒 Carrinhos de Alto Valor (>R$ 200)
                </h3>
                <p class="text-sm text-gray-600">Clientes com carrinhos abandonados de alto valor</p>
            </div>
            <table class="w-full whitespace-no-wrap">
                <thead>
                    <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b bg-gray-50">
                        <th class="px-4 py-3">Cliente</th>
                        <th class="px-4 py-3">Email</th>
                        <th class="px-4 py-3">Valor do Carrinho</th>
                        <th class="px-4 py-3">Itens</th>
                        <th class="px-4 py-3">Última Atualização</th>
                        <th class="px-4 py-3">Ações</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y">
                    @foreach($highValueCarts as $client)
                    <tr class="text-gray-700">
                        <td class="px-4 py-3">
                            <div class="flex items-center">
                                <x-admin.clients.avatar :client="$client" size="sm" />
                                <div class="ml-3">
                                    <p class="font-semibold">{{ $client->name }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-sm">{{ $client->email }}</td>
                        <td class="px-4 py-3">
                            <span class="text-lg font-bold text-green-600">
                                R$ {{ number_format($client->cart_total, 2, ',', '.') }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            {{ $client->cart->products->count() }} {{ $client->cart->products->count() === 1 ? 'item' : 'itens' }}
                        </td>
                        <td class="px-4 py-3 text-sm">
                            {{ $client->cart->updated_at->diffForHumans() }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex space-x-2">
                                <a href="{{ route('admin.reports.clients.show', $client->id) }}" 
                                   class="px-3 py-1 text-xs font-medium text-blue-600 bg-blue-100 rounded-lg hover:bg-blue-200">
                                    Ver Detalhes
                                </a>
                                @if($client->cart->updated_at <= now()->subDays(7))
                                <form method="POST" action="{{ route('admin.reports.clients.send_abandoned_cart_email', $client->id) }}" class="inline">
                                    @csrf
                                    <button type="submit" 
                                            class="px-3 py-1 text-xs font-medium text-orange-600 bg-orange-100 rounded-lg hover:bg-orange-200"
                                            onclick="return confirm('Enviar email de carrinho abandonado?')">
                                        Enviar Email
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Prospects da Wishlist -->
    @if($wishlistProspects->count() > 0)
    <div class="w-full overflow-hidden rounded-lg shadow-xs mb-8">
        <div class="w-full overflow-x-auto">
            <div class="px-4 py-3 bg-gray-50 border-b">
                <h3 class="text-lg font-semibold text-gray-700">
                    ⭐ Prospects da Wishlist
                </h3>
                <p class="text-sm text-gray-600">Clientes com muitos itens na wishlist mas sem pedidos</p>
            </div>
            <table class="w-full whitespace-no-wrap">
                <thead>
                    <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b bg-gray-50">
                        <th class="px-4 py-3">Cliente</th>
                        <th class="px-4 py-3">Email</th>
                        <th class="px-4 py-3">Itens na Wishlist</th>
                        <th class="px-4 py-3">Cadastrado em</th>
                        <th class="px-4 py-3">Ações</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y">
                    @foreach($wishlistProspects as $client)
                    <tr class="text-gray-700">
                        <td class="px-4 py-3">
                            <div class="flex items-center">
                                <x-admin.clients.avatar :client="$client" size="sm" />
                                <div class="ml-3">
                                    <p class="font-semibold">{{ $client->name }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-sm">{{ $client->email }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 text-sm font-medium text-blue-700 bg-blue-100 rounded-full">
                                {{ $client->wishlists_count }} {{ $client->wishlists_count === 1 ? 'item' : 'itens' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            {{ $client->created_at->format('d/m/Y') }}
                        </td>
                        <td class="px-4 py-3">
                            <a href="{{ route('admin.reports.clients.show', $client->id) }}" 
                               class="px-3 py-1 text-xs font-medium text-blue-600 bg-blue-100 rounded-lg hover:bg-blue-200">
                                Ver Detalhes
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Visitantes Recentes -->
    @if($recentVisitors->count() > 0)
    <div class="w-full overflow-hidden rounded-lg shadow-xs mb-8">
        <div class="w-full overflow-x-auto">
            <div class="px-4 py-3 bg-gray-50 border-b">
                <h3 class="text-lg font-semibold text-gray-700">
                    👥 Visitantes Recentes sem Compras
                </h3>
                <p class="text-sm text-gray-600">Clientes ativos recentemente mas que não fizeram pedidos</p>
            </div>
            <table class="w-full whitespace-no-wrap">
                <thead>
                    <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b bg-gray-50">
                        <th class="px-4 py-3">Cliente</th>
                        <th class="px-4 py-3">Email</th>
                        <th class="px-4 py-3">Itens no Carrinho</th>
                        <th class="px-4 py-3">Última Visita</th>
                        <th class="px-4 py-3">Ações</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y">
                    @foreach($recentVisitors as $client)
                    <tr class="text-gray-700">
                        <td class="px-4 py-3">
                            <div class="flex items-center">
                                <x-admin.clients.avatar :client="$client" size="sm" />
                                <div class="ml-3">
                                    <p class="font-semibold">{{ $client->name }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-sm">{{ $client->email }}</td>
                        <td class="px-4 py-3 text-sm">
                            @if($client->cart && $client->cart->products->count() > 0)
                                {{ $client->cart->products->count() }} {{ $client->cart->products->count() === 1 ? 'item' : 'itens' }}
                            @else
                                <span class="text-gray-400">Nenhum item</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm">
                            {{ $client->updated_at->diffForHumans() }}
                        </td>
                        <td class="px-4 py-3">
                            <a href="{{ route('admin.reports.clients.show', $client->id) }}" 
                               class="px-3 py-1 text-xs font-medium text-blue-600 bg-blue-100 rounded-lg hover:bg-blue-200">
                                Ver Detalhes
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    @if($highValueCarts->count() === 0 && $wishlistProspects->count() === 0 && $recentVisitors->count() === 0)
    <div class="flex flex-col items-center justify-center py-12">
        <svg class="w-16 h-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
        </svg>
        <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhum prospect identificado</h3>
        <p class="text-gray-600 text-center max-w-md">
            Não há clientes com alto potencial de conversão no momento. Isso pode indicar que seus clientes estão bem engajados!
        </p>
    </div>
    @endif
</div>
</x-admin-layout>
