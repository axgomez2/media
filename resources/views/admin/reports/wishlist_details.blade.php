<x-admin-layout title="Detalhes da Wishlist">
    <div class="container px-6 mx-auto grid">
        <div class="flex justify-between items-center my-6">
            <h2 class="text-2xl font-semibold text-gray-700">
                Detalhes do Produto em Wishlist
            </h2>
            <a href="{{ route('admin.reports.wishlists') }}" class="px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-gray-600 border border-transparent rounded-lg active:bg-gray-600 hover:bg-gray-700 focus:outline-none focus:shadow-outline-gray">
                Voltar para Lista
            </a>
        </div>

        <!-- Informações do Produto -->
        <div class="p-4 bg-white rounded-lg shadow-md mb-8">
            <div class="flex flex-col md:flex-row">
                <div class="md:w-1/4 mb-4 md:mb-0">
                    @if(isset($product->image_url) && $product->image_url)
                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="rounded-lg w-full">
                    @else
                        <div class="bg-gray-200 rounded-lg w-full h-64 flex items-center justify-center">
                            <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                    @endif
                </div>
                <div class="md:w-3/4 md:pl-6">
                    <h3 class="text-xl font-bold text-gray-800">{{ $product->name }}</h3>
                    
                    <p class="mt-2 text-gray-600">
                        <span class="font-semibold">Preço:</span> 
                        R$ {{ number_format($product->price, 2, ',', '.') }}
                    </p>
                    
                </div>
            </div>
        </div>

        <!-- Estatísticas -->
        <div class="grid gap-6 mb-8 md:grid-cols-2">
            <div class="p-4 bg-white rounded-lg shadow-md">
                <div class="flex items-center">
                    <div class="p-3 rounded-full text-purple-500 bg-purple-100 mr-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="mb-2 text-sm font-medium text-gray-600">
                            Total de Usuários Interessados
                        </p>
                        <p class="text-lg font-semibold text-gray-700">
                            {{ $wishlistUsers->count() }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="p-4 bg-white rounded-lg shadow-md">
                <div class="flex items-center">
                    <div class="p-3 rounded-full text-blue-500 bg-blue-100 mr-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="mb-2 text-sm font-medium text-gray-600">
                            Última Adição à Wishlist
                        </p>
                        <p class="text-lg font-semibold text-gray-700">
                            {{ $wishlistUsers->count() > 0 ? \Carbon\Carbon::parse($wishlistUsers->first()->created_at)->format('d/m/Y H:i') : 'N/A' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabela de Usuários -->
        <div class="w-full overflow-hidden rounded-lg shadow-xs mb-8">
            <div class="w-full overflow-x-auto">
                <table class="w-full whitespace-no-wrap">
                    <thead>
                        <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b bg-gray-50">
                            <th class="px-4 py-3">Usuário</th>
                            <th class="px-4 py-3">Email</th>
                            <th class="px-4 py-3">Adicionado em</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y">
                        @forelse ($wishlistUsers as $user)
                            <tr class="text-gray-700">
                                <td class="px-4 py-3">
                                    <div class="flex items-center text-sm">
                                        <div>
                                            <p class="font-semibold">{{ $user->name }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    {{ $user->email }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    {{ \Carbon\Carbon::parse($user->created_at)->format('d/m/Y H:i') }}
                                </td>
                            </tr>
                        @empty
                            <tr class="text-gray-700">
                                <td class="px-4 py-3 text-sm text-center" colspan="3">
                                    Nenhum usuário encontrado com este produto na wishlist.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-admin-layout>
