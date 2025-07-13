<div class="p-6">
    <div class="flex justify-between items-start">
        <div>
            <h3 class="text-lg font-medium text-gray-900">
                Itens do Carrinho #{{ $cart->id }}
            </h3>
            <p class="mt-1 text-sm text-gray-600">
                Usuário: {{ $cart->user->name ?? 'Usuário Desconhecido' }} ({{ $cart->user->email ?? 'E-mail não disponível' }})
            </p>
        </div>
        <button type="button" class="close-modal text-gray-400 hover:text-gray-500">
            <span class="sr-only">Fechar</span>
            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
    
    <div class="mt-4 border-t border-gray-200 pt-4">
        @if($items->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produto</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Preço</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($items as $item)
                            <tr>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $item->product->name ?? 'Produto não encontrado' }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                ID: #{{ $item->product_id }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        @if($item->product)
                                            R$ {{ number_format($item->product->price, 2, ',', '.') }}
                                        @else
                                            -
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="mt-6 border-t border-gray-200 pt-4">
                <div class="flex justify-between items-center">
                    <span class="text-base font-medium text-gray-900">
                        Total
                    </span>
                    <span class="text-base font-medium text-gray-900">
                        R$ {{ number_format($totalValue, 2, ',', '.') }}
                    </span>
                </div>
                
                <div class="mt-4 text-xs text-gray-500">
                    <p>Carrinho criado em: {{ $cart->created_at->format('d/m/Y H:i') }}</p>
                    <p>Última atualização: {{ $cart->updated_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>
        @else
            <div class="py-8 text-center">
                <p class="text-sm text-gray-500">Este carrinho não possui itens.</p>
            </div>
        @endif
    </div>
    
    <div class="mt-5 sm:mt-6">
        <button type="button" class="close-modal w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-gray-600 text-base font-medium text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 sm:text-sm">
            Fechar
        </button>
    </div>
</div>
