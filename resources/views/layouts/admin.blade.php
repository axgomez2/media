<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'RDV DISCOS') }} - Admin</title>

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

</head>
<body class="font-sans antialiased bg-zinc-200 ">
    <div class="min-h-screen">
        <!-- Toast Notifications -->
        {{-- @if(session('success'))
            <x-site.toast type="success" message="{{ session('success') }}" />
        @endif

        @if(session('error'))
            <x-site.toast type="error" message="{{ session('error') }}" />
        @endif

        @if(session('info'))
            <x-site.toast type="info" message="{{ session('info') }}" />
        @endif

        @if(session('warning'))
            <x-site.toast type="warning" message="{{ session('warning') }}" />
        @endif

        @if($errors->any())
            @foreach($errors->all() as $error)
                <x-site.toast type="error" message="{{ $error }}" />
            @endforeach
        @endif
         --}}
        <!-- Sidebar Component -->
        <x-admin.topbar />
        <x-admin.sidebar />

        <!-- Page Content -->
        <div class="ml-64">



            <!-- Main Content -->
            <main class="p-4">
                {{ $slot }}
            </main>
        </div>
    </div>
</body>
</html>
