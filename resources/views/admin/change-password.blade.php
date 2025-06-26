<x-admin-layout title="Alterar Senha">
    <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-md overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-zinc-700">
            <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100">🔐 Alterar Senha</h3>
            <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">
                Mantenha sua conta segura alterando sua senha regularmente
            </p>
        </div>
        <div class="p-6">
            <!-- Formulário simples de troca de senha -->
            <form method="POST" action="{{ route('admin.update-password') }}" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Senha Atual -->
                <div>
                    <label for="current_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Senha Atual
                    </label>
                    <input
                        type="password"
                        id="current_password"
                        name="current_password"
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                        placeholder="Digite sua senha atual"
                    >
                    @error('current_password')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Nova Senha -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Nova Senha
                    </label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                        minlength="8"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                        placeholder="Digite a nova senha (mínimo 8 caracteres)"
                    >
                    @error('password')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Confirmar Nova Senha -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Confirmar Nova Senha
                    </label>
                    <input
                        type="password"
                        id="password_confirmation"
                        name="password_confirmation"
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                        placeholder="Confirme a nova senha"
                    >
                </div>

                <!-- Botões -->
                <div class="flex items-center justify-between pt-4">
                    <a
                        href="{{ route('admin.dashboard') }}"
                        class="px-4 py-2 text-gray-600 hover:text-gray-800 transition-colors"
                    >
                        ← Voltar ao Dashboard
                    </a>

                    <button
                        type="submit"
                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors"
                    >
                        🔐 Alterar Senha
                    </button>
                </div>
            </form>

            <!-- Mensagens de sucesso/erro -->
            @if(session('success'))
                <div class="mt-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                    ✅ {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mt-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                    ❌ {{ session('error') }}
                </div>
            @endif
        </div>
    </div>
</x-admin-layout>
