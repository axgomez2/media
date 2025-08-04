<x-admin-layout title="Relatórios">
<div class="container px-6 mx-auto grid">
    <h2 class="my-6 text-2xl font-semibold text-gray-700">
        Relatórios
    </h2>

    <div class="grid gap-6 mb-8 md:grid-cols-2 xl:grid-cols-3">
        <!-- Card Relatório de Discos -->
        <div class="p-4 bg-white rounded-lg shadow-md  ">
            <div class="flex items-center">
                <div class="p-3 rounded-full text-blue-500 bg-blue-100 mr-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"></path>
                    </svg>
                </div>
                <div>
                    <p class="mb-2 text-sm font-medium text-gray-600">
                        Relatório de Discos
                    </p>
                    <p class="text-lg font-semibold text-gray-700">
                        Inventário e Valores
                    </p>
                </div>
            </div>
            <p class="text-gray-600 mt-4">
                Visualize dados completos sobre discos em estoque, valores de compra, venda e lucro potencial.
            </p>
            <div class="mt-6">
                <a href="{{ route('admin.reports.vinyl') }}"
                   class="px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-blue-600 border border-transparent rounded-lg active:bg-blue-600 hover:bg-blue-700 focus:outline-none focus:shadow-outline-blue">
                    Ver Relatório
                </a>
            </div>
        </div>

        <!-- Card Relatório de Clientes -->
        <div class="p-4 bg-white rounded-lg shadow-md ">
            <div class="flex items-center">
                <div class="p-3 rounded-full text-indigo-500 bg-indigo-100 mr-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                    </svg>
                </div>
                <div>
                    <p class="mb-2 text-sm font-medium text-gray-600">
                        Relatório de Clientes
                    </p>
                    <p class="text-lg font-semibold text-gray-700">
                        {{ $clientsCount ?? 0 }} clientes
                    </p>
                </div>
            </div>
            <p class="text-gray-600 mt-4">
                Visualize e gerencie dados completos dos clientes cadastrados, incluindo pedidos e estatísticas.
            </p>
            <div class="mt-6">
                <a href="{{ route('admin.reports.clients.index') }}"
                   class="px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-indigo-600 border border-transparent rounded-lg active:bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:shadow-outline-indigo">
                    Ver Relatório
                </a>
            </div>
        </div>

        <!-- Card Relatório de Carrinhos -->
        <div class="p-4 bg-white rounded-lg shadow-md ">
            <div class="flex items-center">
                <div class="p-3 rounded-full text-green-500 bg-green-100 mr-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <div>
                    <p class="mb-2 text-sm font-medium text-gray-600">
                        Relatório de Carrinhos
                    </p>
                    <p class="text-lg font-semibold text-gray-700">
                        {{ $cartItemsCount ?? 0 }} itens
                    </p>
                </div>
            </div>
            <p class="text-gray-600 mt-4">
                Analise quais discos estão nos carrinhos dos clientes e identifique tendências de compra.
            </p>
            <div class="mt-6">
                <a href="{{ route('admin.reports.carts') }}"
                   class="px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-green-600 border border-transparent rounded-lg active:bg-green-600 hover:bg-green-700 focus:outline-none focus:shadow-outline-green">
                    Ver Relatório
                </a>
            </div>
        </div>

        <!-- Card Relatório de Wishlist -->
        <div class="p-4 bg-white rounded-lg shadow-md ">
            <div class="flex items-center">
                <div class="p-3 rounded-full text-purple-500 bg-purple-100 mr-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                    </svg>
                </div>
                <div>
                    <p class="mb-2 text-sm font-medium text-gray-600">
                        Relatório de Wishlist
                    </p>
                    <p class="text-lg font-semibold text-gray-700">
                        {{ $wishlistItemsCount ?? 0 }} itens
                    </p>
                </div>
            </div>
            <p class="text-gray-600 mt-4">
                Descubra quais discos são mais desejados pelos clientes e estão em suas listas de desejos.
            </p>
            <div class="mt-6">
                <a href="{{ route('admin.reports.wishlists') }}"
                   class="px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-lg active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple">
                    Ver Relatório
                </a>
            </div>
        </div>

        <!-- Card Relatório de Wantlist -->
        <div class="p-4 bg-white rounded-lg shadow-md ">
            <div class="flex items-center">
                <div class="p-3 rounded-full text-yellow-500 bg-yellow-100 mr-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                    </svg>
                </div>
                <div>
                    <p class="mb-2 text-sm font-medium text-gray-600">
                        Relatório de Wantlist
                    </p>
                    <p class="text-lg font-semibold text-gray-700">
                        {{ $wantlistItemsCount ?? 0 }} itens
                    </p>
                </div>
            </div>
            <p class="text-gray-600 mt-4">
                Veja quais discos os clientes estão procurando e considere adicionar ao seu estoque.
            </p>
            <div class="mt-6">
                <a href="{{ route('admin.reports.wantlists') }}"
                   class="px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-yellow-600 border border-transparent rounded-lg active:bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:shadow-outline-yellow">
                    Ver Relatório
                </a>
            </div>
        </div>

        <!-- Card Relatório de Visualizações -->
        <div class="p-4 bg-white rounded-lg shadow-md ">
            <div class="flex items-center">
                <div class="p-3 rounded-full text-red-500 bg-red-100 mr-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                </div>
                <div>
                    <p class="mb-2 text-sm font-medium text-gray-600">
                        Relatório de Visualizações
                    </p>
                    <p class="text-lg font-semibold text-gray-700">
                        {{ $viewsCount ?? 0 }} visualizações
                    </p>
                </div>
            </div>
            <p class="text-gray-600 mt-4">
                Analise quais discos recebem mais visualizações e o comportamento dos visitantes.
            </p>
            <div class="mt-6">
                <a href="{{ route('admin.reports.views') }}"
                   class="px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-red-600 border border-transparent rounded-lg active:bg-red-600 hover:bg-red-700 focus:outline-none focus:shadow-outline-red">
                    Ver Relatório
                </a>
            </div>
        </div>
    </div>
</div>
</x-admin-layout>
