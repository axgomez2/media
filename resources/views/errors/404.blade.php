<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Página não encontrada - {{ config('app.name') }}</title>
    @vite(['resources/css/admin.css'])
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8 text-center">
            <div>
                <div class="mx-auto h-24 w-24 text-indigo-600">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9.172 16.172a4 4 0 015.656 0M9 12h6m-6-4h6m2 5.291A7.962 7.962 0 0112 15c-2.137 0-4.146-.832-5.636-2.364M6.343 7.343A7.963 7.963 0 0112 5c4.418 0 8 3.582 8 8a7.95 7.95 0 01-2.343 5.657m0 0L9.172 16.172m8.485 1.485a1 1 0 01-1.414 0l-4.243-4.243a1 1 0 010-1.414l4.243-4.243a1 1 0 011.414 0l4.243 4.243a1 1 0 010 1.414l-4.243 4.243z"></path>
                    </svg>
                </div>
                <h1 class="mt-6 text-6xl font-bold text-gray-900">404</h1>
                <h2 class="mt-2 text-3xl font-bold text-gray-900">Página não encontrada</h2>
                <p class="mt-2 text-sm text-gray-600">
                    Desculpe, não conseguimos encontrar a página que você está procurando.
                </p>
            </div>

            <div class="mt-8 space-y-4">
                <button onclick="history.back()"
                        class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Voltar
                </button>

                <a href="{{ route('admin.reports.index') }}"
                   class="w-full flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a2 2 0 012-2h4a2 2 0 012 2v4"></path>
                    </svg>
                    Ir para Relatórios
                </a>
            </div>

            <div class="mt-8 text-xs text-gray-500">
                <p>Código do erro: 404</p>
                <p>Timestamp: {{ now()->format('d/m/Y H:i:s') }}</p>
                @if(config('app.debug'))
                    <p class="mt-2">URL solicitada: {{ request()->fullUrl() }}</p>
                @endif
            </div>
        </div>
    </div>
</body>
</html>
