<x-admin-layout title="Adicionar novo disco">
<!-- Meta tags para URLs -->
<meta name="store-vinyl-url" content="{{ route('admin.vinyls.store') }}">
<meta name="vinyl-index-url" content="{{ route('admin.vinyls.index') }}">
<meta name="complete-vinyl-url" content="{{ route('admin.vinyls.complete', ':id') }}">
<meta name="csrf-token" content="{{ csrf_token() }}">

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('vinylCreateManager', () => ({
        loading: false,
        showYouTubeModal: false,
        youtubeResults: [],
        activeTrackIndex: null,
        isLoading: false,
        tracks: [],

        init() {
            // Listener para o evento de busca no YouTube
            this.$el.addEventListener('search-youtube', (event) => {
                const { trackName, artistName, trackIndex } = event.detail;
                this.searchYouTube(trackName, artistName, trackIndex);
            });
        },

        search() {
            this.loading = true;
            document.getElementById('search-form').submit();
        },

        async searchYouTube(trackName, artistName, trackIndex) {
            this.activeTrackIndex = trackIndex;
            this.isLoading = true;
            const query = `${artistName} ${trackName}`;

            try {
                const response = await fetch('{{ route('youtube.search') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ query })
                });

                if (!response.ok) throw new Error('Network response was not ok');
                const data = await response.json();
                if (data.error) throw new Error(data.error);

                this.youtubeResults = data;
                this.showYouTubeModal = true;
            } catch (error) {
                console.error('Erro ao pesquisar no YouTube:', error);
                alert('Erro ao pesquisar no YouTube. Tente novamente.');
            } finally {
                this.isLoading = false;
            }
        },

        selectYouTubeVideo(video) {
            if (this.activeTrackIndex !== null) {
                // Encontrar o input de YouTube URL correspondente
                const youtubeInput = document.querySelector(`input[name="tracks[${this.activeTrackIndex}][youtube_url]"]`);
                if (youtubeInput) {
                    youtubeInput.value = `https://www.youtube.com/watch?v=${video.id.videoId}`;
                }
            }
            this.closeYouTubeModal();
        },

        closeYouTubeModal() {
            this.showYouTubeModal = false;
            this.youtubeResults = [];
            this.activeTrackIndex = null;
        }
    }));
});
</script>

<div
    x-data="vinylCreateManager"
    class="p-4">


    <div class="p-4 bg-white border border-gray-200 rounded-lg shadow">
        <h2 class="text-xl font-semibold mb-4 pl-2 text-gray-900 ">Pesquisar novo disco:</h2>

       <x-admin.vinyls-components.search-discogs :query="$query" />

        <div id="searchResults" class="mt-6">
            @if($selectedRelease)
                <!-- Selected Release Content -->
            <x-admin.vinyls-components.selected-release :release="$selectedRelease" />
            @elseif(count($searchResults) > 0)
                <!-- Search Results -->
               <x-admin.vinyls-components.search-result :searchResults="$searchResults" :query="$query" />
            @elseif($query)
                <div class="p-4 mb-4 text-sm text-blue-800 rounded-lg bg-blue-50" role="alert">
                    <div class="flex items-center">
                        <svg class="flex-shrink-0 w-4 h-4 me-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
                        </svg>
                        <span>Nenhum resultado encontrado para "{{ $query }}".</span>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Modal para resultados do YouTube -->
    <div x-show="showYouTubeModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" x-cloak>
        <div class="flex items-center justify-center min-h-screen px-4 text-center">
            <div x-show="showYouTubeModal" @click="closeYouTubeModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-900 bg-opacity-50 transition-opacity"></div>

            <div x-show="showYouTubeModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Selecionar Vídeo do YouTube
                            </h3>
                            <div class="mt-4 space-y-3 max-h-96 overflow-y-auto">
                                <template x-for="result in youtubeResults" :key="result.id.videoId">
                                    <div class="p-3 hover:bg-gray-100 rounded-lg cursor-pointer flex items-start space-x-4" @click="selectYouTubeVideo(result)">
                                        <img :src="result.snippet.thumbnails.default.url" class="w-24 h-24 object-cover rounded-md">
                                        <div>
                                            <h4 class="font-semibold text-gray-800" x-text="result.snippet.title"></h4>
                                            <p class="text-sm text-gray-500" x-text="result.snippet.channelTitle"></p>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" @click="closeYouTubeModal" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Fechar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
</x-admin-layout>
@push('scripts')
<script>
// Variável global para armazenar o ID do vinyl salvo
let savedVinylId = null;

// Função para salvar o disco com JavaScript puro
function saveVinyl(releaseId) {
    // Pegar o botão que foi clicado
    const saveButton = event.target.closest('button');

    // Mostrar loading no botão
    if (saveButton) {
        saveButton.disabled = true;
        saveButton.innerHTML = '<svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Salvando...';
    }

    // Obter o CSRF token do Laravel
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    // Fazer requisição para salvar o disco
    fetch('{{ route("admin.vinyls.store") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ release_id: releaseId })
    })
    .then(response => {
        if (!response.ok) {
            return response.text().then(text => {
                throw new Error('Erro HTTP: ' + response.status + ' ' + text);
            });
        }
        return response.json();
    })
    .then(data => {
        console.log('Resposta:', data);

        // Dependendo do status da resposta, fazer uma ação diferente
        if (data.status === 'success' && data.vinyl_id) {
            // Redirecionar para a página de completar o cadastro
            window.location.href = '{{ route("admin.vinyls.complete", ":id") }}'.replace(':id', data.vinyl_id);
        } else if (data.status === 'exists') {
            // Mostrar alerta se o disco já existir
            alert(data.message || 'Este disco já está cadastrado no sistema.');
            // Restaurar o botão
            if (saveButton) {
                saveButton.disabled = false;
                saveButton.innerHTML = '<span>Salvar disco</span>';
            }
        } else {
            // Mostrar erro genérico
            alert(data.message || 'Ocorreu um erro ao salvar o disco.');
            // Restaurar o botão
            if (saveButton) {
                saveButton.disabled = false;
                saveButton.innerHTML = '<span>Salvar disco</span>';
            }
        }
    })
    .catch(error => {
        // Tratar erros de requisição
        console.error('Erro:', error);
        alert(error.message || 'Ocorreu um erro ao salvar o disco.');

        // Restaurar o botão
        if (saveButton) {
            saveButton.disabled = false;
            saveButton.innerHTML = '<span>Salvar disco</span>';
        }
    });
}
</script>
@endpush
