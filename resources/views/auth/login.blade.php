<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Tailwind CDN (ou use o app.css compilado do Vite, se preferir) -->
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        body {
            font-family: system-ui, -apple-system, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        }
    </style>
</head>
<body class="bg-gray-100 text-gray-800">

    <div class="min-h-screen flex items-center justify-center">
        <div class="w-full max-w-md p-8 space-y-6 bg-white rounded-lg shadow-md">

            <h1 class="text-2xl font-bold text-center">Login</h1>

            @if(session('error'))
                <div class="text-red-500 text-center text-sm">
                    {{ session('error') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium mb-1">Email</label>
                    <input type="email" name="email" id="email" required autofocus
                           class="w-full border px-3 py-2 rounded focus:outline-none focus:ring focus:border-indigo-500"
                           placeholder="seu@email.com">
                    @error('email')
                        <span class="text-sm text-red-500">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="password" class="block text-sm font-medium mb-1">Senha</label>
                    <input type="password" name="password" id="password" required
                           class="w-full border px-3 py-2 rounded focus:outline-none focus:ring focus:border-indigo-500"
                           placeholder="••••••••">
                    @error('password')
                        <span class="text-sm text-red-500">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-4 flex items-center">
                    <input type="checkbox" name="remember" id="remember" class="mr-2">
                    <label for="remember" class="text-sm">Lembrar-me</label>
                </div>

                <div>
                    <button type="submit"
                            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2 px-4 rounded transition">
                        Entrar
                    </button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>
