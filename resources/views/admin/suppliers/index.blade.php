<x-admin-layout title="Gerenciar Fornecedores">
<div class="px-4 sm:px-6 lg:px-8 py-6">
    <!-- Cabeçalho -->
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold text-gray-800">Fornecedores</h1>
        <a href="{{ route('admin.suppliers.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow">
            <i class="fas fa-plus mr-2"></i> Novo Fornecedor
        </a>
    </div>

    <!-- Alertas -->
    <div class="space-y-4" x-data="{ showSuccess: true, showError: true }">
        @if(session('success'))
        <div x-show="showSuccess" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
            <span @click="showSuccess = false" class="absolute top-0 bottom-0 right-0 px-4 py-3 cursor-pointer">
                <svg class="fill-current h-6 w-6 text-green-500" viewBox="0 0 20 20">
                    <path d="M14.348 5.652a1 1 0 010 1.414L11.414 10l2.934 2.934a1 1 0 11-1.414 1.414L10 11.414l-2.934 2.934a1 1 0 11-1.414-1.414L8.586 10 5.652 7.066a1 1 0 011.414-1.414L10 8.586l2.934-2.934a1 1 0 011.414 0z"/>
                </svg>
            </span>
        </div>
        @endif

        @if(session('error'))
        <div x-show="showError" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
            <span @click="showError = false" class="absolute top-0 bottom-0 right-0 px-4 py-3 cursor-pointer">
                <svg class="fill-current h-6 w-6 text-red-500" viewBox="0 0 20 20">
                    <path d="M14.348 5.652a1 1 0 010 1.414L11.414 10l2.934 2.934a1 1 0 11-1.414 1.414L10 11.414l-2.934 2.934a1 1 0 11-1.414-1.414L8.586 10 5.652 7.066a1 1 0 011.414-1.414L10 8.586l2.934-2.934a1 1 0 011.414 0z"/>
                </svg>
            </span>
        </div>
        @endif
    </div>

    

    <!-- Tabela -->
    <div class="overflow-x-auto px-6 ">
        <table class="w-full text-sm text-left text-gray-500 border border-indigo-600 bg-white shadow-md  ">
            <thead class="text-xs text-gray-700 uppercase bg-gray-200 border border-indigo-600  ">
                <tr>
                    <th scope="col" class="px-4 py-3">id</th>
                    <th scope="col" class="px-4 py-3">nome</th>
                    <th scope="col" class="px-4 py-3">email</th>
                    <th scope="col" class="px-4 py-3">telefone</th>
                    <th scope="col" class="px-4 py-3">ações</th>
                </tr>   
            </thead>
            <tbody>
                @foreach ($suppliers as $supplier)
                <tr class="border-b ">
                    <th scope="row" class="px-4 py-3 font-medium text-gray-900 whitespace-nowrap ">{{ $supplier->id }}</th>
                    <td class="px-4 py-3 text-gray-700">{{ $supplier->name }}</td>
                    
                    <td class="px-4 py-3 text-gray-700">{{ $supplier->email }}</td>
                    <td class="px-4 py-3 text-gray-700">{{ $supplier->phone }}</td>
                    <td class="px-6 py-4 space-x-2">
                        <a href="{{ route('admin.suppliers.edit', $supplier->id) }}"
                           class="inline-flex items-center px-3 py-1 text-xs font-medium text-white bg-zinc-500 hover:bg-zinc-600 rounded">
                            <i class="fas fa-edit mr-1"></i> Editar
                        </a>

                        <form action="{{ route('admin.suppliers.destroy', $supplier->id) }}"
                              method="POST" class="inline"
                              onsubmit="return confirm('Tem certeza que deseja excluir este fornecedor?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="inline-flex items-center px-3 py-1 text-xs font-medium text-white bg-red-600 rounded hover:bg-red-700">
                                <i class="fas fa-trash mr-1"></i> Excluir
                            </button>
                        </form>
                    </td>
                </tr>
           @endforeach
         
            </tbody>
        </table>
    </div>
</div>
</x-admin-layout>
