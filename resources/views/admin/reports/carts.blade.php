<x-admin-layout title="Relatório de Carrinhos Abertos">
    <div class="container px-6 mx-auto grid">
        <h2 class="my-6 text-2xl font-semibold text-gray-700">
            Carrinhos Abertos
        </h2>

        <!-- Estatísticas Gerais -->
        <div class="grid gap-6 mb-8 md:grid-cols-3">
            <div class="p-4 bg-white rounded-lg shadow-md">
                <div class="flex items-center">
                    <div class="p-3 rounded-full text-green-500 bg-green-100 mr-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="mb-2 text-sm font-medium text-gray-600">
                            Total de Carrinhos Abertos
                        </p>
                        <p class="text-lg font-semibold text-gray-700">
                            {{ $carts->count() }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="p-4 bg-white rounded-lg shadow-md">
                <div class="flex items-center">
                    <div class="p-3 rounded-full text-blue-500 bg-blue-100 mr-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="mb-2 text-sm font-medium text-gray-600">
                            Total de Itens
                        </p>
                        <p class="text-lg font-semibold text-gray-700">
                            {{ $carts->sum('items_count') }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="p-4 bg-white rounded-lg shadow-md">
                <div class="flex items-center">
                    <div class="p-3 rounded-full text-purple-500 bg-purple-100 mr-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="mb-2 text-sm font-medium text-gray-600">
                            Valor Total
                        </p>
                        <p class="text-lg font-semibold text-gray-700">
                            R$ {{ number_format($carts->sum('total_value'), 2, ',', '.') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabela de Carrinhos Abertos -->
        <div class="w-full overflow-hidden rounded-lg shadow-xs mb-8">
            <div class="w-full overflow-x-auto">
                <table class="w-full whitespace-no-wrap">
                    <thead>
                        <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b bg-gray-50">
                            <th class="px-4 py-3">ID</th>
                            <th class="px-4 py-3">Usuário</th>
                            <th class="px-4 py-3">Email</th>
                            <th class="px-4 py-3">Qtd. Items</th>
                            <th class="px-4 py-3">Valor Total</th>
                            <th class="px-4 py-3">Criado em</th>
                            <th class="px-4 py-3">Atualizado em</th>
                            <th class="px-4 py-3">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y">
                        @forelse ($carts as $cart)
                            <tr class="text-gray-700">
                                <td class="px-4 py-3 text-sm">
                                    #{{ $cart->id }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center text-sm">
                                        <div>
                                            <p class="font-semibold">{{ $cart->user->name ?? 'Usuário Desconhecido' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    {{ $cart->user->email ?? 'E-mail não disponível' }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    {{ $cart->items_count }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    R$ {{ number_format($cart->total_value, 2, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    {{ $cart->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    {{ $cart->updated_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <button 
                                        data-cart-id="{{ $cart->id }}"
                                        class="view-cart-items px-3 py-1 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-blue-600 border border-transparent rounded-md active:bg-blue-600 hover:bg-blue-700 focus:outline-none focus:shadow-outline-blue">
                                        Ver Itens
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr class="text-gray-700">
                                <td class="px-4 py-3 text-sm text-center" colspan="8">
                                    Não há carrinhos abertos no momento.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal para exibição dos itens do carrinho -->
    <div id="cartItemsModal" class="fixed inset-0 z-30 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>

            <!-- Modal panel -->
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                <div id="cartItemsContent">
                    <!-- Conteúdo será carregado via AJAX -->
                    <div class="flex justify-center items-center p-8">
                        <svg class="animate-spin h-10 w-10 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Modal elements
            const modal = document.getElementById('cartItemsModal');
            const modalContent = document.getElementById('cartItemsContent');
            
            // Open modal buttons
            document.querySelectorAll('.view-cart-items').forEach(button => {
                button.addEventListener('click', function() {
                    const cartId = this.getAttribute('data-cart-id');
                    openCartItemsModal(cartId);
                });
            });
            
            // Close modal when clicking outside
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    closeModal();
                }
            });
            
            // Function to open modal and load cart items
            function openCartItemsModal(cartId) {
                // Show modal
                modal.classList.remove('hidden');
                
                // Show loading state
                modalContent.innerHTML = `
                    <div class="flex justify-center items-center p-8">
                        <svg class="animate-spin h-10 w-10 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                `;
                
                // Fetch cart items
                fetch(`/admin/reports/carrinhos-abertos/${cartId}/items`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            modalContent.innerHTML = data.html;
                            
                            // Add event listener to close button
                            const closeButton = document.querySelector('.close-modal');
                            if (closeButton) {
                                closeButton.addEventListener('click', closeModal);
                            }
                        } else {
                            modalContent.innerHTML = `
                                <div class="p-6">
                                    <h3 class="text-lg font-medium text-red-600 mb-4">Erro ao carregar itens do carrinho</h3>
                                    <div class="mt-5 sm:mt-6">
                                        <button type="button" class="close-modal w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-gray-600 text-base font-medium text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 sm:text-sm">
                                            Fechar
                                        </button>
                                    </div>
                                </div>
                            `;
                            
                            document.querySelector('.close-modal').addEventListener('click', closeModal);
                        }
                    })
                    .catch(error => {
                        console.error('Erro ao buscar itens do carrinho:', error);
                        modalContent.innerHTML = `
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-red-600 mb-4">Erro ao carregar itens do carrinho</h3>
                                <p class="text-sm text-gray-500">Ocorreu um erro ao buscar os itens do carrinho. Por favor, tente novamente.</p>
                                <div class="mt-5 sm:mt-6">
                                    <button type="button" class="close-modal w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-gray-600 text-base font-medium text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 sm:text-sm">
                                        Fechar
                                    </button>
                                </div>
                            </div>
                        `;
                        
                        document.querySelector('.close-modal').addEventListener('click', closeModal);
                    });
            }
            
            // Function to close the modal
            function closeModal() {
                modal.classList.add('hidden');
            }
        });
    </script>
    @endpush
</x-admin-layout>
