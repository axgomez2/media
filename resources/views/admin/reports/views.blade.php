<x-admin-layout title="Relatório de Visualizações">
    <div class="container px-6 mx-auto grid">
        <h2 class="my-6 text-2xl font-semibold text-gray-700">
            Relatório de Visualizações de Discos
        </h2>

        <!-- Estatísticas Gerais -->
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
                            {{ $vinylViews->sum('view_count') }}
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
                            {{ $vinylViews->sum('unique_users') }}
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
                            {{ $vinylViews->sum('unique_ips') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabela de Visualizações de Discos -->
        <div class="w-full overflow-hidden rounded-lg shadow-xs mb-8">
            <div class="w-full overflow-x-auto">
                <table class="w-full whitespace-no-wrap">
                    <thead>
                        <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b bg-gray-50 ">
                            <th class="px-4 py-3">Disco</th>
                            <th class="px-4 py-3">Visualizações</th>
                            <th class="px-4 py-3">Usuários Únicos</th>
                            <th class="px-4 py-3">IPs Únicos</th>
                            <th class="px-4 py-3">Última Visualização</th>
                            <th class="px-4 py-3">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y ">
                        @forelse ($vinylViews as $item)
                            <tr class="text-gray-700 ">
                                <td class="px-4 py-3">
                                    <div class="flex items-center text-sm">
                                        <div>
                                            <p class="font-semibold">{{ $item->title }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    {{ $item->view_count }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    {{ $item->unique_users }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    {{ $item->unique_ips }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    {{ \Carbon\Carbon::parse($item->last_viewed)->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <a href="{{ route('admin.reports.view_details', $item->master_id) }}" 
                                       class="px-3 py-1 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-red-600 border border-transparent rounded-md active:bg-red-600 hover:bg-red-700 focus:outline-none focus:shadow-outline-red">
                                        Detalhes
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr class="text-gray-700 ">
                                <td class="px-4 py-3 text-sm text-center" colspan="6">
                                    Nenhuma visualização de disco registrada.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-admin-layout>
