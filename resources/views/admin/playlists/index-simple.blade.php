<x-admin-layout title="Playlists">
<div class="p-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Playlists</h1>
            <p class="text-gray-600">Gerencie playlists de DJs e Charts</p>
        </div>

        <div class="flex gap-3">
            <a href="{{ route('admin.playlists.create', ['type' => 'dj']) }}"
               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                DJ Set
            </a>
            <a href="{{ route('admin.playlists.create', ['type' => 'chart']) }}"
               class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Chart
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm border p-4 mb-6">
        <div class="flex gap-4">
            <a href="{{ route('admin.playlists.index', ['type' => 'all']) }}"
               class="px-4 py-2 rounded-lg {{ ($type ?? 'all') === 'all' ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                Todas
            </a>
            <a href="{{ route('admin.playlists.index', ['type' => 'dj']) }}"
               class="px-4 py-2 rounded-lg {{ ($type ?? 'all') === 'dj' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                DJ Sets
            </a>
            <a href="{{ route('admin.playlists.index', ['type' => 'chart']) }}"
               class="px-4 py-2 rounded-lg {{ ($type ?? 'all') === 'chart' ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                Charts
            </a>
        </div>
    </div>

    <!-- Playlists Grid -->
    @if(isset($playlists) && $playlists->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($playlists as $playlist)
                <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
                    <!-- Header -->
                    <div class="p-4 border-b">
                        <div class="flex justify-between items-start mb-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $playlist->type === 'dj' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                {{ $playlist->type === 'dj' ? 'DJ Set' : 'Chart' }}
                            </span>
                            @if(!$playlist->is_active)
                                <span class="w-2 h-2 bg-red-500 rounded-full"></span>
                            @endif
                        </div>
                        <h3 class="font-semibold text-gray-900 mb-1">{{ $playlist->title }}</h3>
                        @if($playlist->type === 'chart' && $playlist->chart_date)
                            <p class="text-sm text-gray-500">{{ $playlist->chart_date->format('d/m/Y') }}</p>
                        @endif
                    </div>

                    <!-- Description -->
                    @if($playlist->description)
                        <div class="p-4 border-b">
                            <p class="text-sm text-gray-600 line-clamp-3">{{ $playlist->description }}</p>
                        </div>
                    @endif

                    <!-- Actions -->
                    <div class="p-4 bg-gray-50">
                        <div class="flex gap-2">
                            <a href="{{ route('admin.playlists.show', $playlist) }}"
                               class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded text-sm text-center">
                                Ver
                            </a>
                            <a href="{{ route('admin.playlists.edit', $playlist) }}"
                               class="flex-1 bg-gray-600 hover:bg-gray-700 text-white px-3 py-2 rounded text-sm text-center">
                                Editar
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{ $playlists->links() }}
        </div>
    @else
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"></path>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhuma playlist encontrada</h3>
            <p class="mt-1 text-sm text-gray-500">Comece criando uma nova playlist.</p>
            <div class="mt-6 flex gap-3 justify-center">
                <a href="{{ route('admin.playlists.create', ['type' => 'dj']) }}"
                   class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                    Criar DJ Set
                </a>
                <a href="{{ route('admin.playlists.create', ['type' => 'chart']) }}"
                   class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg">
                    Criar Chart
                </a>
            </div>
        </div>
    @endif
</div>
</x-admin-layout>
