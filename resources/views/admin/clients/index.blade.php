<x-admin-layout title="Gerenciamento de Clientes">
    <div class="container px-6 mx-auto grid">
        <div class="flex justify-between items-center my-6">
            <h2 class="text-2xl font-semibold text-gray-700">
                Clientes
            </h2>
            <a href="{{ route('admin.clients.create') }}" class="px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-lg active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple">
                Adicionar Cliente
            </a>
        </div>

        <div class="w-full overflow-hidden rounded-lg shadow-xs mb-8">
            <div class="w-full overflow-x-auto">
                <table class="w-full whitespace-no-wrap">
                    <thead>
                        <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b bg-gray-50">
                            <th class="px-4 py-3">Nome</th>
                            <th class="px-4 py-3">Email</th>
                            <th class="px-4 py-3">Data de Cadastro</th>
                            <th class="px-4 py-3">Email Verificado</th>
                            <th class="px-4 py-3">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y">
                        @forelse ($clients as $client)
                            <tr class="text-gray-700">
                                <td class="px-4 py-3">
                                    <p class="font-semibold">{{ $client->name }}</p>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    {{ $client->email }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    {{ $client->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    @if ($client->email_verified_at)
                                        <span class="px-2 py-1 font-semibold leading-tight text-green-700 bg-green-100 rounded-full">
                                            Sim
                                        </span>
                                    @else
                                        <span class="px-2 py-1 font-semibold leading-tight text-red-700 bg-red-100 rounded-full">
                                            Não
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <a href="{{ route('admin.clients.edit', $client->id) }}" class="px-3 py-1 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-blue-600 border border-transparent rounded-md active:bg-blue-600 hover:bg-blue-700 focus:outline-none focus:shadow-outline-blue">Editar</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-3 text-center text-gray-500">
                                    Nenhum cliente encontrado.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 text-xs font-semibold tracking-wide text-gray-500 uppercase border-t bg-gray-50 sm:grid-cols-9">
                {{ $clients->links() }}
            </div>
        </div>
    </div>
</x-admin-layout>
