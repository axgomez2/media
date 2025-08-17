<x-admin-layout title="Configurar Autenticação de Dois Fatores">
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Configurar Autenticação de Dois Fatores</h1>
        <p class="mt-2 text-gray-600">Adicione uma camada extra de segurança à sua conta</p>
    </div>

    <div class="bg-white shadow rounded-lg">
        <div class="p-6">
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
                    <!-- Passo 1: Instalar aplicativo -->
                    <div class="border-l-4 border-blue-500 pl-4">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">
                            1. Instale um aplicativo autenticador
                        </h3>
                        <p class="text-gray-600 mb-3">
                            Baixe e instale um dos seguintes aplicativos no seu smartphone:
                        </p>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="text-center p-3 border rounded-lg">
                                <div class="text-2xl mb-2">📱</div>
                                <div class="font-medium">Google Authenticator</div>
                                <div class="text-sm text-gray-500">iOS / Android</div>
                            </div>
                            <div class="text-center p-3 border rounded-lg">
                                <div class="text-2xl mb-2">🔐</div>
                                <div class="font-medium">Authy</div>
                                <div class="text-sm text-gray-500">iOS / Android</div>
                            </div>
                            <div class="text-center p-3 border rounded-lg">
                                <div class="text-2xl mb-2">🛡️</div>
                                <div class="font-medium">Microsoft Authenticator</div>
                                <div class="text-sm text-gray-500">iOS / Android</div>
                            </div>
                        </div>
                    </div>

                    <!-- Passo 2: Escanear QR Code -->
                    <div class="border-l-4 border-green-500 pl-4">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">
                            2. Escaneie o código QR
                        </h3>
                        <p class="text-gray-600 mb-4">
                            Use o aplicativo autenticador para escanear este código QR:
                        </p>

                        <div class="flex flex-col md:flex-row items-center space-y-4 md:space-y-0 md:space-x-6">
                            <div class="bg-white p-4 border-2 border-gray-200 rounded-lg">
                                <img src="{{ $qrCodeUrl }}" alt="QR Code" class="w-48 h-48">
                            </div>

                            <div class="flex-1">
                                <p class="text-sm text-gray-600 mb-2">
                                    Não consegue escanear? Digite manualmente:
                                </p>
                                <div class="bg-gray-100 p-3 rounded-md">
                                    <code class="text-sm font-mono break-all">{{ $secretKey }}</code>
                                </div>
                                <button onclick="copySecret()" class="mt-2 text-sm text-blue-600 hover:text-blue-500">
                                    📋 Copiar chave secreta
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Passo 3: Verificar código -->
                    <div class="border-l-4 border-yellow-500 pl-4">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">
                            3. Digite o código de verificação
                        </h3>
                        <p class="text-gray-600 mb-4">
                            Digite o código de 6 dígitos que aparece no seu aplicativo:
                        </p>

                        <form action="{{ route('admin.two-factor.enable') }}" method="POST" class="space-y-4">
                            @csrf
                            <input type="hidden" name="secret" value="{{ $secretKey }}">

                            <div>
                                <label for="code" class="block text-sm font-medium text-gray-700 mb-1">
                                    Código de verificação
                                </label>
                                <input type="text" id="code" name="code" maxlength="6" pattern="[0-9]{6}"
                                       class="w-32 px-3 py-2 border border-gray-300 rounded-md text-center text-xl tracking-widest focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="000000" required autofocus>
                            </div>

                            <div class="flex space-x-4">
                                <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                                    ✅ Ativar 2FA
                                </button>
                                <a href="{{ route('admin.dashboard') }}" class="bg-gray-300 text-gray-700 px-6 py-2 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500">
                                    Cancelar
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
        </div>
    </div>

<script>
function copySecret() {
    const secretText = '{{ $secretKey }}';
    navigator.clipboard.writeText(secretText).then(function() {
        alert('Chave secreta copiada para a área de transferência!');
    });
}

// Auto-submit quando o código de 6 dígitos for digitado
document.getElementById('code').addEventListener('input', function(e) {
    if (e.target.value.length === 6) {
        e.target.form.submit();
    }
});
</script>
</x-admin-layout>
