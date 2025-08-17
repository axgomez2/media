<x-admin-layout title="Autenticação de Dois Fatores">
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 flex items-center">
            <svg class="w-8 h-8 mr-3 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
            </svg>
            Autenticação de Dois Fatores
        </h1>
        <p class="mt-2 text-gray-600">Sua conta está protegida com 2FA</p>
    </div>

    <div class="bg-white shadow rounded-lg">
        <div class="p-6">
                @if (session('success'))
                    <div class="mb-6 bg-green-50 border border-green-200 rounded-md p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-green-800">
                                    {{ session('success') }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                @if (session('recovery_codes'))
                    <div class="mb-6 bg-yellow-50 border border-yellow-200 rounded-md p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-yellow-800 mb-2">
                                    Códigos de Recuperação Gerados
                                </h3>
                                <p class="text-sm text-yellow-700 mb-3">
                                    Guarde estes códigos em local seguro. Você pode usar cada um apenas uma vez:
                                </p>
                                <div class="bg-white p-3 rounded border grid grid-cols-2 gap-2">
                                    @foreach (session('recovery_codes') as $code)
                                        <code class="text-sm font-mono bg-gray-100 px-2 py-1 rounded">{{ $code }}</code>
                                    @endforeach
                                </div>
                                <button onclick="printRecoveryCodes()" class="mt-2 text-sm text-yellow-700 hover:text-yellow-600">
                                    🖨️ Imprimir códigos
                                </button>
                            </div>
                        </div>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-6 bg-red-50 border border-red-200 rounded-md p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800">
                                    @foreach ($errors->all() as $error)
                                        {{ $error }}
                                    @endforeach
                                </h3>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="space-y-6">
                    <!-- Status atual -->
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-lg font-medium text-green-800">
                                    2FA está ativo desde {{ $user->two_factor_confirmed_at->format('d/m/Y H:i') }}
                                </h3>
                                <p class="text-green-700">
                                    Sua conta está protegida com autenticação de dois fatores.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Ações disponíveis -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Ver códigos de recuperação -->
                        <div class="border border-gray-200 rounded-lg p-4">
                            <h4 class="font-medium text-gray-900 mb-2">Códigos de Recuperação</h4>
                            <p class="text-sm text-gray-600 mb-3">
                                Visualize ou regenere seus códigos de recuperação.
                            </p>
                            <a href="{{ route('admin.two-factor.recovery-codes') }}"
                               class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                📋 Ver Códigos
                            </a>
                        </div>

                        <!-- Desativar 2FA -->
                        <div class="border border-red-200 rounded-lg p-4">
                            <h4 class="font-medium text-gray-900 mb-2">Desativar 2FA</h4>
                            <p class="text-sm text-gray-600 mb-3">
                                Remover a proteção de dois fatores da sua conta.
                            </p>
                            <button onclick="showDisableForm()"
                                    class="inline-flex items-center px-3 py-2 border border-red-300 shadow-sm text-sm leading-4 font-medium rounded-md text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                🚫 Desativar 2FA
                            </button>
                        </div>
                    </div>

                    <!-- Formulário para desativar 2FA (oculto por padrão) -->
                    <div id="disable-form" class="hidden border border-red-200 rounded-lg p-4 bg-red-50">
                        <h4 class="font-medium text-red-900 mb-3">Confirmar Desativação do 2FA</h4>
                        <p class="text-sm text-red-700 mb-4">
                            ⚠️ <strong>Atenção:</strong> Desativar o 2FA reduzirá a segurança da sua conta.
                            Digite sua senha atual e um código do aplicativo para confirmar.
                        </p>

                        <form action="{{ route('admin.two-factor.disable') }}" method="POST" class="space-y-4">
                            @csrf
                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                                    Senha atual
                                </label>
                                <input type="password" id="password" name="password" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                            </div>

                            <div>
                                <label for="disable_code" class="block text-sm font-medium text-gray-700 mb-1">
                                    Código do aplicativo
                                </label>
                                <input type="text" id="disable_code" name="code" maxlength="6" pattern="[0-9]{6}" required
                                       class="w-32 px-3 py-2 border border-gray-300 rounded-md text-center text-xl tracking-widest focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                       placeholder="000000">
                            </div>

                            <div class="flex space-x-4">
                                <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                                    Confirmar Desativação
                                </button>
                                <button type="button" onclick="hideDisableForm()" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500">
                                    Cancelar
                                </button>
                            </div>
                        </form>
                    </div>
            </div>
        </div>
    </div>

<script>
function showDisableForm() {
    document.getElementById('disable-form').classList.remove('hidden');
    document.getElementById('password').focus();
}

function hideDisableForm() {
    document.getElementById('disable-form').classList.add('hidden');
}

function printRecoveryCodes() {
    window.print();
}
</script>
</x-admin-layout>
