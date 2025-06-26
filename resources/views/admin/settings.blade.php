<x-admin-layout title="Configurações">
    <div class="mt-6">
        <h2 class="text-xl font-semibold mb-4 text-zinc-900 ">Configurações da loja</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <!-- Categorias -->
            <div class="bg-white  p-5 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300">
                <div class="flex items-center justify-center h-12 w-12 rounded-md bg-emerald-500 text-white mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="font-bold text-lg mb-2 text-zinc-900 ">Categorias dos discos</h3>
                <p class="text-zinc-600  text-sm mb-4">Adicionar, editar e remover categorias dos discos.</p>
                <a href="{{ route('admin.cat-style-shop.index') }}" class="text-emerald-600 hover:text-emerald-500 text-sm font-medium">Acessar &rarr;</a>
            </div>
            
            <!-- Midia -->
            <div class="bg-white  p-5 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300">
                <div class="flex items-center justify-center h-12 w-12 rounded-md bg-blue-500 text-white mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                </div>
                <h3 class="font-bold text-lg mb-2 text-zinc-900 ">Midia dos discos</h3>
                <p class="text-zinc-600  text-sm mb-4">Gerenciar do estado de midia dos discos.</p>
                <a href="{{ route('admin.midia-status.index') }}" class="text-blue-600 hover:text-blue-500 text-sm font-medium">Acessar &rarr;</a>
            </div>
            
            <!-- Capas -->
            <div class="bg-white  p-5 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300">
                <div class="flex items-center justify-center h-12 w-12 rounded-md bg-amber-500 text-white mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <h3 class="font-bold text-lg mb-2 text-zinc-900 ">Capa dos discos</h3>
                <p class="text-zinc-600  text-sm mb-4">Gerenciar do estado de capa dos discos.</p>
                <a href="{{ route('admin.cover-status.index') }}" class="text-amber-600 hover:text-amber-500 text-sm font-medium">Acessar &rarr;</a>
            </div>
            
            <!-- Configurações -->
            <div class="bg-white  p-5 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300">
                <div class="flex items-center justify-center h-12 w-12 rounded-md bg-purple-500 text-white mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <h3 class="font-bold text-lg mb-2 text-zinc-900 ">Fornecedores</h3>
                <p class="text-zinc-600  text-sm mb-4">Gerenciar fornecedores da loja.</p>
                <a href="{{ route('admin.suppliers.index') }}" class="text-purple-600 hover:text-purple-500 text-sm font-medium">Acessar &rarr;</a>
            </div>
            
        
        </div>
    </div>
</x-admin-layout>
