<x-admin-layout title="Códigos de Recuperação 2FA">
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 flex items-center">
            <svg class="w-8 h-8 mr-3 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 8A6 6 0 006 8v2H5a3 3 0 00-3 3v4a3 3 0 003 3h10a3 3 0 003-3v-4a3 3 0 00-3-3h-1V8zM9 8a3 3 0 016 0v2H9V8z" clip-rule="evenodd"></path>
            </svg>
            Códigos de Recuperação
        </h1>
        <p class="mt-2 text-gray-600">Use estes códigos se perder acesso ao seu aplicativo autenticador</p>
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
                    <div class="mb-6 bg-blue-50 border border-blue-200 rounded-md p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-blue-800 mb-2">
                                    Novos Códigos de Recuperação Gerados
                                </h3>
                                <p class="text-sm text-blue-700">
                                    Os códigos antigos foram invalidados. Guarde estes novos códigos em local seguro.
                                </p>
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
                    <!-- Instruções importantes -->
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-yellow-800 mb-2">Instruções Importantes</h3>
                                <ul class="text-sm text-yellow-700 space-y-1">
                                    <li>• Cada código pode ser usado apenas uma vez</li>
                                    <li>• Guarde estes códigos em local seguro (cofre, gerenciador de senhas)</li>
                                    <li>• Use apenas se perder acesso ao seu aplicativo autenticador</li>
                                    <li>• Considere regenerar os códigos se suspeitar que foram comprometidos</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Códigos de recuperação -->
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2H4zm2 6a2 2 0 104 0 2 2 0 00-4 0zm6 0a2 2 0 104 0 2 2 0 00-4 0z" clip-rule="evenodd"></path>
                            </svg>
                            Seus Códigos de Recuperação
                        </h3>

                        @if (count($recoveryCodes) > 0)
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-4">
                                @foreach ($recoveryCodes as $index => $code)
                                    <div class="bg-white border border-gray-300 rounded-md p-3 flex items-center justify-between">
                                        <code class="text-lg font-mono text-gray-900">{{ $code }}</code>
                                        <button onclick="copyCode('{{ $code }}')" class="text-gray-400 hover:text-gray-600 ml-2">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M8 3a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1z"></path>
                                                <path d="M6 3a2 2 0 00-2 2v11a2 2 0 002 2h8a2 2 0 002-2V5a2 2 0 00-2-2 3 3 0 01-3 3H9a3 3 0 01-3-3z"></path>
                                            </svg>
                                        </button>
                                    </div>
                                @endforeach
                            </div>

                            <div class="flex flex-wrap gap-3">
                                <button onclick="copyAllCodes()" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    📋 Copiar Todos
                                </button>
                                <button onclick="printCodes()" class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500">
                                    🖨️ Imprimir
                                </button>
                                <button onclick="downloadCodes()" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                                    💾 Baixar
                                </button>
                            </div>
                        @else
                            <div class="text-center py-8">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhum código disponível</h3>
                                <p class="mt-1 text-sm text-gray-500">Você precisa regenerar seus códigos de recuperação.</p>
                            </div>
                        @endif
                    </div>

                    <!-- Ações -->
                    <div class="flex flex-wrap gap-4">
                        <button onclick="showRegenerateForm()" class="bg-yellow-600 text-white px-6 py-2 rounded-md hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500">
                            🔄 Regenerar Códigos
                        </button>
                        <a href="{{ route('admin.two-factor.show') }}" class="bg-gray-300 text-gray-700 px-6 py-2 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500">
                            ← Voltar
                        </a>
                    </div>

                    <!-- Formulário para regenerar códigos (oculto por padrão) -->
                    <div id="regenerate-form" class="hidden border border-yellow-200 rounded-lg p-4 bg-yellow-50">
                        <h4 class="font-medium text-yellow-900 mb-3">Regenerar Códigos de Recuperação</h4>
                        <p class="text-sm text-yellow-700 mb-4">
                            ⚠️ <strong>Atenção:</strong> Regenerar os códigos invalidará todos os códigos atuais.
                            Digite sua senha atual para confirmar.
                        </p>

                        <form action="{{ route('admin.two-factor.recovery-codes.regenerate') }}" method="POST" class="space-y-4">
                            @csrf
                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                                    Senha atual
                                </label>
                                <input type="password" id="password" name="password" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                            </div>

                            <div class="flex space-x-4">
                                <button type="submit" class="bg-yellow-600 text-white px-4 py-2 rounded-md hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500">
                                    Confirmar Regeneração
                                </button>
                                <button type="button" onclick="hideRegenerateForm()" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500">
                                    Cancelar
                                </button>
                            </div>
                        </form>
                    </div>
            </div>
        </div>
    </div>

<script>
const recoveryCodes = @json($recoveryCodes);

function copyCode(code) {
    navigator.clipboard.writeText(code).then(function() {
        showToast('Código copiado!');
    });
}

function copyAllCodes() {
    const allCodes = recoveryCodes.join('\n');
    navigator.clipboard.writeText(allCodes).then(function() {
        showToast('Todos os códigos copiados!');
    });
}

function printCodes() {
    window.print();
}

function downloadCodes() {
    const content = `Códigos de Recuperação 2FA - {{ config('app.name') }}
Gerado em: ${new Date().toLocaleString('pt-BR')}
Usuário: {{ auth()->user()->email }}

IMPORTANTE:
- Cada código pode ser usado apenas uma vez
- Guarde em local seguro
- Use apenas se perder acesso ao aplicativo autenticador

Códigos:
${recoveryCodes.map((code, index) => `${index + 1}. ${code}`).join('\n')}

---
Este arquivo contém informações sensíveis. Mantenha-o seguro.`;

    const blob = new Blob([content], { type: 'text/plain' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'recovery-codes-2fa.txt';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);

    showToast('Códigos baixados!');
}

function showRegenerateForm() {
    document.getElementById('regenerate-form').classList.remove('hidden');
    document.getElementById('password').focus();
}

function hideRegenerateForm() {
    document.getElementById('regenerate-form').classList.add('hidden');
}

function showToast(message) {
    // Criar toast simples
    const toast = document.createElement('div');
    toast.className = 'fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded-md shadow-lg z-50';
    toast.textContent = message;
    document.body.appendChild(toast);

    setTimeout(() => {
        document.body.removeChild(toast);
    }, 3000);
}
</script>
</x-admin-layout>
