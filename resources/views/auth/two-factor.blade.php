<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Verificação de Dois Fatores</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Tailwind CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        body {
            font-family: system-ui, -apple-system, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        }
    </style>
</head>
<body class="bg-gray-100 text-gray-800">
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <div class="mx-auto h-12 w-12 flex items-center justify-center rounded-full bg-blue-100">
                <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
            </div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Verificação de Dois Fatores
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Digite o código de 6 dígitos do seu aplicativo autenticador
            </p>
        </div>

        <div class="mt-8 space-y-6">
            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 rounded-md p-4">
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

            <!-- Formulário principal de verificação -->
            <form class="space-y-6" action="{{ route('two-factor.verify.post') }}" method="POST">
                @csrf
                <div>
                    <label for="code" class="sr-only">Código de verificação</label>
                    <input id="code" name="code" type="text" maxlength="6" pattern="[0-9]{6}"
                           class="appearance-none rounded-md relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm text-center text-2xl tracking-widest"
                           placeholder="000000" required autofocus>
                </div>

                <div>
                    <button type="submit" class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Verificar Código
                    </button>
                </div>
            </form>

            <!-- Opção de código de recuperação -->
            <div class="text-center">
                <button type="button" onclick="toggleRecoveryForm()" class="text-sm text-blue-600 hover:text-blue-500">
                    Usar código de recuperação
                </button>
            </div>

            <!-- Formulário de código de recuperação (oculto por padrão) -->
            <form id="recovery-form" class="space-y-6 hidden" action="{{ route('two-factor.recovery') }}" method="POST">
                @csrf
                <div>
                    <label for="recovery_code" class="block text-sm font-medium text-gray-700">
                        Código de Recuperação
                    </label>
                    <input id="recovery_code" name="recovery_code" type="text"
                           class="mt-1 appearance-none rounded-md relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
                           placeholder="Digite seu código de recuperação">
                </div>

                <div>
                    <button type="submit" class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-gray-600 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                        Usar Código de Recuperação
                    </button>
                </div>

                <div class="text-center">
                    <button type="button" onclick="toggleRecoveryForm()" class="text-sm text-gray-600 hover:text-gray-500">
                        Voltar para código do aplicativo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleRecoveryForm() {
    const mainForm = document.querySelector('form[action="{{ route('two-factor.verify.post') }}"]');
    const recoveryForm = document.getElementById('recovery-form');

    if (recoveryForm.classList.contains('hidden')) {
        mainForm.classList.add('hidden');
        recoveryForm.classList.remove('hidden');
        document.getElementById('recovery_code').focus();
    } else {
        recoveryForm.classList.add('hidden');
        mainForm.classList.remove('hidden');
        document.getElementById('code').focus();
    }
}

// Auto-submit quando o código de 6 dígitos for digitado
document.getElementById('code').addEventListener('input', function(e) {
    if (e.target.value.length === 6) {
        e.target.form.submit();
    }
});
</script>
</body>
</html>
