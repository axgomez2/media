<x-admin-layout title="Detalhes da Wantlist">
    <div class="container px-6 mx-auto grid">
        <div class="flex justify-between items-center my-6">
            <h2 class="text-2xl font-semibold text-gray-700">
                Detalhes do Disco em Wantlist
            </h2>
            <a href="{{ route('admin.reports.wantlists') }}" class="px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-gray-600 border border-transparent rounded-lg active:bg-gray-600 hover:bg-gray-700 focus:outline-none focus:shadow-outline-gray">
                Voltar para Lista
            </a>
        </div>

        <!-- Informações do Disco -->
        <div class="p-4 bg-white rounded-lg shadow-md mb-8">
            <div class="flex flex-col md:flex-row">
                <div class="md:w-1/4 mb-4 md:mb-0">
                    @if($vinyl->image_url)
                        <img src="{{ $vinyl->image_url }}" alt="{{ $vinyl->title }}" class="rounded-lg w-full">
                    @else
                        <div class="bg-gray-200 rounded-lg w-full h-64 flex items-center justify-center">
                            <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                    @endif
                </div>
                <div class="md:w-3/4 md:pl-6">
                    <h3 class="text-xl font-bold text-gray-800 ">{{ $vinyl->title }}</h3>
                    
                    <p class="mt-2 text-gray-600 ">
                        <span class="font-semibold">Artistas:</span> 
                        {{ $vinyl->artists->pluck('name')->implode(', ') }}
                    </p>
                    
                    <div class="mt-4 flex flex-wrap gap-2">
                        <a href="{{ route('admin.vinyls.index', ['search' => $vinyl->title]) }}" class="px-3 py-1 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-blue-600 border border-transparent rounded-md active:bg-blue-600 hover:bg-blue-700 focus:outline-none focus:shadow-outline-blue">
                            Ver Discos Relacionados
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estatísticas -->
        <div class="grid gap-6 mb-8 md:grid-cols-2">
            <div class="p-4 bg-white rounded-lg shadow-md">
                <div class="flex items-center">
                    <div class="p-3 rounded-full text-yellow-500 bg-yellow-100 mr-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="mb-2 text-sm font-medium text-gray-600">
                            Total de Usuários Interessados
                        </p>
                        <p class="text-lg font-semibold text-gray-700">
                            {{ $wantlistUsers->count() }}
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
                            Última Adição à Wantlist
                        </p>
                        <p class="text-lg font-semibold text-gray-700">
                            {{ $wantlistUsers->count() > 0 ? \Carbon\Carbon::parse($wantlistUsers->first()->created_at)->format('d/m/Y H:i') : 'N/A' }}
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
                        @forelse ($wantlistUsers as $user)
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
                                    Nenhum usuário encontrado com este disco na wantlist.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-admin-layout>
