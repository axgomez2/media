<x-admin-layout title="Detalhes de Visualizações">
    <div class="container px-6 mx-auto grid">
        <div class="flex justify-between items-center my-6">
            <h2 class="text-2xl font-semibold text-gray-700">
                Detalhes de Visualizações do Disco
            </h2>
            <a href="{{ route('admin.reports.views') }}" class="px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-gray-600 border border-transparent rounded-lg active:bg-gray-600 hover:bg-gray-700 focus:outline-none focus:shadow-outline-gray">
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
        <div class="grid gap-6 mb-8 md:grid-cols-3">
            <div class="p-4 bg-white rounded-lg shadow-md ">
                <div class="flex items-center">
                    <div class="p-3 rounded-full text-red-500 bg-red-100 mr-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="mb-2 text-sm font-medium text-gray-600">
                            Total de Visualizações
                        </p>
                        <p class="text-lg font-semibold text-gray-700">
                            {{ $views->count() }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="p-4 bg-white rounded-lg shadow-md ">
                <div class="flex items-center">
                    <div class="p-3 rounded-full text-blue-500 bg-blue-100 mr-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="mb-2 text-sm font-medium text-gray-600">
                            Usuários Únicos
                        </p>
                        <p class="text-lg font-semibold text-gray-700">
                            {{ $views->whereNotNull('user_name')->count() }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="p-4 bg-white rounded-lg shadow-md ">
                <div class="flex items-center">
                    <div class="p-3 rounded-full text-purple-500 bg-purple-100 mr-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="mb-2 text-sm font-medium text-gray-600">
                            IPs Únicos
                        </p>
                        <p class="text-lg font-semibold text-gray-700">
                            {{ $views->unique('ip_address')->count() }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráfico de Visualizações por Dia (Placeholder) -->
        <div class="p-4 bg-white rounded-lg shadow-md mb-8">
            <h4 class="mb-4 font-semibold text-gray-800 ">
                Visualizações por Dia
            </h4>
            <div class="h-64 bg-gray-100  rounded-lg flex items-center justify-center">
                <p class="text-gray-500 ">
                    Gráfico de visualizações diárias será implementado aqui.
                </p>
            </div>
        </div>

        <!-- Tabela de Visualizações Detalhadas -->
        <div class="w-full overflow-hidden rounded-lg shadow-xs mb-8">
            <div class="w-full overflow-x-auto">
                <table class="w-full whitespace-no-wrap">
                    <thead>
                        <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b bg-gray-50 ">
                            <th class="px-4 py-3">Data/Hora</th>
                            <th class="px-4 py-3">Usuário</th>
                            <th class="px-4 py-3">IP</th>
                            <th class="px-4 py-3">Dispositivo</th>
                            <th class="px-4 py-3">Navegador</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y ">
                        @forelse ($views as $view)
                            <tr class="text-gray-700 ">
                                <td class="px-4 py-3 text-sm">
                                    {{ \Carbon\Carbon::parse($view->viewed_at)->format('d/m/Y H:i:s') }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center text-sm">
                                    <div>
                                        <p class="font-semibold">{{ $view->user ? $view->user->name : 'Anônimo' }}</p>
                                        @if($view->user)
                                            <p class="text-xs text-gray-600 ">{{ $view->user->email }}</p>
                                        @endif
                                    </div>
                                </div>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    {{ $view->ip_address }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    {{ $view->device_type ?? 'Não identificado' }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    {{ $view->browser ?? 'Não identificado' }}
                                </td>
                            </tr>
                        @empty
                            <tr class="text-gray-700 ">
                                <td class="px-4 py-3 text-sm text-center" colspan="5">
                                    Nenhuma visualização registrada para este disco.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Paginação -->
            @if(method_exists($views, 'hasPages') && $views->hasPages())
                <div class="px-4 py-3 text-xs font-semibold tracking-wide text-gray-500 uppercase border-t bg-gray-50 ">
                    {{ method_exists($views, 'links') ? $views->links() : '' }}
                </div>
            @endif
        </div>
    </div>
</x-admin-layout>
