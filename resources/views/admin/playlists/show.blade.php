<x-admin-layout title="{{ $playlist->title }}">
<div class="p-6 max-w-4xl mx-auto">
    <!-- Header -->
    <div class="flex justify-between items-start mb-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.playlists.index') }}"
               class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <h1 class="text-2xl font-bold text-gray-900">{{ $playlist->title }}</h1>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        {{ $playlist->is_dj ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                        {{ $playlist->type_formatted }}
                    </span>
                    @if(!$playlist->is_active)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                            Inativa
                        </span>
                    @endif
                </div>
                @if($playlist->is_chart && $playlist->chart_date)
                    <p class="text-gray-600">{{ $playlist->chart_date->format('d/m/Y') }}</p>
                @endif
            </div>
        </div>

        <div class="flex gap-3">
            <a href="{{ route('admin.playlists.edit', $playlist) }}"
               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Editar
            </a>
            <form action="{{ route('admin.playlists.toggle-status', $playlist) }}" method="POST" class="inline">
                @csrf
                @method('PATCH')
                <button type="submit"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                    {{ $playlist->is_active ? 'Desativar' : 'Ativar' }}
                </button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Description -->
            @if($playlist->description)
                <div class="bg-white rounded-lg shadow-sm border p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-3">
                        {{ $playlist->is_dj ? 'Resumo' : 'Descrição' }}
                    </h2>
                    <p class="text-gray-700 leading-relaxed">{{ $playlist->description }}</p>
                </div>
            @endif

            <!-- Tracks -->
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-semibold text-gray-900">Faixas</h2>
                    <span class="text-sm text-gray-500">{{ $playlist->tracks->count() }}/10</span>
                </div>

                @if($playlist->tracks->count() > 0)
                    <div class="space-y-3">
                        @foreach($playlist->tracks as $track)
                            <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-lg">
                                <div class="w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center text-sm font-medium">
                                    {{ $track->position }}
                                </div>
                                <div class="flex-1">
                                    <h3 class="font-medium text-gray-900">{{ $track->product->name ?? 'Produto não encontrado' }}</h3>
                                    @if($track->product && $track->product->productable)
                                        <p class="text-sm text-gray-600">{{ $track->product->productable->title }}</p>
                                        @if($track->product->productable->artists)
                                            <p class="text-xs text-gray-500">
                                                {{ $track->product->productable->artists->pluck('name')->join(', ') }}
                                            </p>
                                        @endif
                                    @endif
                                </div>
                                @if($track->product)
                                    <a href="#" class="text-blue-600 hover:text-blue-800 text-sm">
                                        Ver produto
                                    </a>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhuma faixa</h3>
                        <p class="mt-1 text-sm text-gray-500">Esta playlist ainda não possui faixas.</p>
                        <div class="mt-6">
                            <a href="{{ route('admin.playlists.edit', $playlist) }}"
                               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                                Adicionar Faixas
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Info -->
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Informações</h2>

                <div class="space-y-3">
                    <div>
                        <span class="text-sm font-medium text-gray-500">Tipo</span>
                        <p class="text-sm text-gray-900">{{ $playlist->type_formatted }}</p>
                    </div>

                    @if($playlist->is_chart && $playlist->chart_date)
                        <div>
                            <span class="text-sm font-medium text-gray-500">Data</span>
                            <p class="text-sm text-gray-900">{{ $playlist->chart_date->format('d/m/Y') }}</p>
                        </div>
                    @endif

                    <div>
                        <span class="text-sm font-medium text-gray-500">Status</span>
                        <p class="text-sm">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $playlist->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $playlist->is_active ? 'Ativa' : 'Inativa' }}
                            </span>
                        </p>
                    </div>

                    <div>
                        <span class="text-sm font-medium text-gray-500">Criada em</span>
                        <p class="text-sm text-gray-900">{{ $playlist->created_at->format('d/m/Y H:i') }}</p>
                    </div>

                    <div>
                        <span class="text-sm font-medium text-gray-500">Atualizada em</span>
                        <p class="text-sm text-gray-900">{{ $playlist->updated_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
            </div>

            @if($playlist->is_dj && $playlist->social_links)
                <!-- Social Links -->
                <div class="bg-white rounded-lg shadow-sm border p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Redes Sociais</h2>

                    <div class="space-y-3">
                        @foreach($playlist->social_links as $platform => $url)
                            @if($url)
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium text-gray-700 capitalize">{{ $platform }}</span>
                                    <a href="{{ $url }}" target="_blank"
                                       class="text-blue-600 hover:text-blue-800 text-sm">
                                        Visitar
                                        <svg class="w-3 h-3 inline ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                        </svg>
                                    </a>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Actions -->
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Ações</h2>

                <div class="space-y-3">
                    <a href="{{ route('admin.playlists.edit', $playlist) }}"
                       class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-center block">
                        Editar Playlist
                    </a>

                    <form action="{{ route('admin.playlists.toggle-status', $playlist) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <button type="submit"
                                class="w-full px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                            {{ $playlist->is_active ? 'Desativar' : 'Ativar' }}
                        </button>
                    </form>

                    <form action="{{ route('admin.playlists.destroy', $playlist) }}" method="POST"
                          onsubmit="return confirm('Tem certeza que deseja excluir esta playlist?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="w-full px-4 py-2 border border-red-300 text-red-700 rounded-lg hover:bg-red-50">
                            Excluir Playlist
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</x-admin-layout>
