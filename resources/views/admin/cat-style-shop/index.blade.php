<x-admin-layout title="Gerenciar Categorias de Discos">
    
    <section class=" mt-4 ">
        
        <div class="mx-auto max-w-screen-xl px-4 lg:px-4">
            <!-- Start coding here -->
            <div class=" relative  sm:rounded-lg overflow-hidden">
                <div class="flex flex-col md:flex-row items-center justify-between space-y-3 md:space-y-0 md:space-x-4 p-4">
                    <h1 class="text-2xl font-semibold text-gray-800 ml-2">Categorias de Discos</h1>
                    <a href="{{ route('admin.cat-style-shop.create') }}"
                       class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 mr-2 mt-2">
                        <i class="fas fa-plus mr-2 text-white"></i> Nova Categoria
                    </a>
                </div>
             
                <div class="overflow-x-auto px-6 ">
                    <table class="w-full text-sm text-left text-gray-500 border border-indigo-600 bg-white shadow-md  ">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-200 border border-indigo-600  ">
                            <tr>
                                <th scope="col" class="px-4 py-3">id</th>
                                <th scope="col" class="px-4 py-3">nome da categoria</th>
                                <th scope="col" class="px-4 py-3">tipo</th>
                                <th scope="col" class="px-4 py-3">slug</th>
                                <th scope="col" class="px-4 py-3">ações</th>
                            </tr>   
                        </thead>
                        <tbody>
                            @foreach ($categories as $category)
                            <tr class="border-b ">
                                <th scope="row" class="px-4 py-3 font-medium text-gray-900 whitespace-nowrap ">{{ $category->id }}</th>
                                <td class="px-4 py-3 text-gray-700">{{ $category->name }}</td>
                                <td class="px-4 py-3"> <span class="inline-flex px-2 py-1 text-xs font-medium text-white bg-blue-600 rounded-full">Categoria</span></td>
                                <td class="px-4 py-3 text-gray-700">{{ $category->slug }}</td>
                                <td class="px-6 py-4 space-x-2">
                                    <a href="{{ route('admin.cat-style-shop.edit', $category->id) }}"
                                       class="inline-flex items-center px-3 py-1 text-xs font-medium text-white bg-zinc-500 hover:bg-zinc-600 rounded">
                                        <i class="fas fa-edit mr-1"></i> Editar
                                    </a>

                                    <form action="{{ route('admin.cat-style-shop.destroy', $category->id) }}"
                                          method="POST" class="inline"
                                          onsubmit="return confirm('Tem certeza que deseja excluir esta categoria?');">
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
        </div>
    </section>
 
</x-admin-layout>
