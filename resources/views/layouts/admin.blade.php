<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'RDV DISCOS')) - Admin</title>

    <!-- Favicon -->
    @if($favicon = \App\Models\StoreInformation::getInstance()->favicon_url)
    <link rel="icon" href="{{ $favicon }}" type="image/x-icon">
    @endif

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Scripts -->
    @vite(['resources/css/admin.css', 'resources/js/admin.js'])

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Custom Styles for Playlist Search -->
    <style>
        .search-dropdown {
            scrollbar-width: thin;
            scrollbar-color: #cbd5e0 #f7fafc;
        }

        .search-dropdown::-webkit-scrollbar {
            width: 6px;
        }

        .search-dropdown::-webkit-scrollbar-track {
            background: #f7fafc;
        }

        .search-dropdown::-webkit-scrollbar-thumb {
            background: #cbd5e0;
            border-radius: 3px;
        }

        .search-dropdown::-webkit-scrollbar-thumb:hover {
            background: #a0aec0;
        }

        .track-input:focus {
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
    </style>

</head>
<body class="font-sans antialiased bg-zinc-200 ">
    <div class="min-h-screen">
        <!-- Toast Notifications -->
        @if(session('success'))
            <div class="fixed top-4 right-4 z-50 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="fixed top-4 right-4 z-50 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg">
                {{ session('error') }}
            </div>
        @endif

        @if(session('info'))
            <div class="fixed top-4 right-4 z-50 bg-blue-500 text-white px-6 py-3 rounded-lg shadow-lg">
                {{ session('info') }}
            </div>
        @endif

        @if(session('warning'))
            <div class="fixed top-4 right-4 z-50 bg-yellow-500 text-white px-6 py-3 rounded-lg shadow-lg">
                {{ session('warning') }}
            </div>
        @endif

        @if($errors->any())
            @foreach($errors->all() as $error)
                <div class="fixed top-4 right-4 z-50 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg">
                    {{ $error }}
                </div>
            @endforeach
        @endif
        <!-- Sidebar Component -->
        <x-admin.topbar />
        <x-admin.sidebar />

        <!-- Page Content -->
        <div class="ml-64 pt-16">
            <!-- Main Content -->
            <main class="p-4">
               {{$slot}}
            </main>
        </div>
    </div>
    @stack('scripts')
</body>
</html>
