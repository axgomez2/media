<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dados inválidos - {{ config('app.name') }}</title>
    @vite(['resources/css/admin.css'])
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8 text-center">
            <div>
                <div class="mx-auto h-24 w-24 text-orange-600">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h1 class="mt-6 text-6xl font-bold text-gray-900">422</h1>
                <h2 class="mt-2 text-3xl font-bold text-gray-900">Dados inválidos</h2>
                <p class="mt-2 text-sm text-gray-600">
                    Os dados enviados não puderam ser processados. Verifique as informações e tente novamente.
                </p>
            </div>

            <div class="mt-8 space-y-4">
                <button onclick="history.back()"
                        class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-colors duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Voltar e corrigir
                </button>

                <a href="{{ route('admin.reports.index') }}"
                   class="w-full flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-colors duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a2 2 0 012-2h4a2 2 0 012 2v4"></path>
                    </svg>
                    Ir para Relatórios
                </a>
            </div>

            <div class="mt-8 text-xs text-gray-500">
                <p>Código do erro: 422</p>
                <p>Timestamp: {{ now()->format('d/m/Y H:i:s') }}</p>
                @if(config('app.debug'))
                    <p class="mt-2">URL solicitada: {{ request()->fullUrl() }}</p>
                @endif
            </div>
        </div>
    </div>
</body>
</html>
