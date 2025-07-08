<x-admin-layout title="Editar Faixas">
    <div x-data="trackManager" class="container mx-auto px-4 py-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-semibold text-gray-900">Editar Faixas: {{ $vinyl->title }}</h2>
            <a href="{{ route('admin.vinyls.index') }}"
                class="text-gray-900 bg-white border border-gray-300 focus:outline-none hover:bg-gray-100 focus:ring-4 focus:ring-gray-200 font-medium rounded-lg text-sm px-5 py-2.5">Voltar</a>
        </div>

        <div class="p-4 mb-6 bg-white border border-gray-200 rounded-lg shadow-sm">
            <div class="text-sm">
                <span class="font-bold">Artista:</span> {{ $vinyl->artists->pluck('name')->join(', ') }} |
                <span class="font-bold">Ano:</span> {{ $vinyl->release_year }}
            </div>
        </div>

        <form action="{{ route('admin.vinyls.update-tracks', $vinyl->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="p-6 bg-white border border-gray-200 rounded-lg shadow-sm">
                <div id="tracks-container" class="space-y-4">
                    <template x-for="(track, index) in tracks" :key="index">
                        <div class="grid grid-cols-12 gap-4 items-center p-3 bg-gray-50 rounded-lg border">
                            <input type="hidden" :name="'tracks['+index+'][id]'" :value="track.id">

                            <div class="col-span-12 sm:col-span-4">
                                <label :for="'track_name_'+index" class="sr-only">Nome da Faixa</label>
                                <input type="text" x-model="track.name" :name="'tracks['+index+'][name]'" :id="'track_name_'+index" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" placeholder="Nome da Faixa" required>
                            </div>

                            <div class="col-span-12 sm:col-span-1">
                                <label :for="'track_duration_'+index" class="sr-only">Duração</label>
                                <input type="text" x-model="track.duration" :name="'tracks['+index+'][duration]'" :id="'track_duration_'+index" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" placeholder="Duração">
                            </div>

                            <div class="col-span-12 sm:col-span-6">
                                <label :for="'track_youtube_'+index" class="sr-only">URL YouTube</label>
                                <div class="flex">
                                    <input type="url" x-model="track.youtube_url" :name="'tracks['+index+'][youtube_url]'" :id="'track_youtube_'+index" class="rounded-none rounded-s-lg bg-white border border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500 block flex-1 min-w-0 w-full text-sm p-2.5" placeholder="URL do YouTube">
                                    <button type="button" @click="searchYouTube(index)" :disabled="isLoading" class="inline-flex items-center px-3 text-sm text-white bg-blue-600 border border-s-0 border-blue-600 rounded-e-md hover:bg-blue-700 disabled:opacity-50">
                                        <template x-if="!isLoading">
                                            <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                                            </svg>
                                        </template>
                                        <template x-if="isLoading">
                                            <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                            </svg>
                                        </template>
                                        <span class="sr-only">Pesquisar</span>
                                    </button>
                                </div>
                            </div>

                            <div class="col-span-12 sm:col-span-1 flex justify-end">
                                <button type="button" @click="removeTrack(index)" class="p-2.5 text-sm font-medium text-white bg-red-600 rounded-lg border border-red-600 hover:bg-red-700 focus:ring-4 focus:outline-none focus:ring-red-300">
                                    <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 18 20">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 5h16M7 8v8m4-8v8M7 1h4a1 1 0 0 1 1 1v3H6V2a1 1 0 0 1 1-1ZM3 5h12v13a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V5Z"/>
                                    </svg>
                                    <span class="sr-only">Excluir Faixa</span>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                <button type="button" class="mt-4 text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center inline-flex items-center" @click="addTrack">
                    <svg class="w-4 h-4 me-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 5.75V14.25M5.75 10H14.25"/>
                    </svg>
                    Adicionar Faixa
                </button>
            </div>
            <div class="flex justify-end p-4 mt-4 border-t border-gray-200">
                <button type="submit" class="text-white bg-green-700 hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">Salvar Alterações</button>
            </div>
        </form>

        <!-- Modal para resultados do YouTube -->
        <div x-show="showModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" x-cloak>
            <div class="flex items-center justify-center min-h-screen px-4 text-center">

                <div x-show="showModal" @click="closeModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-900 bg-opacity-50 transition-opacity"></div>

                <div x-show="showModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="relative inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    Selecionar Vídeo do YouTube
                                </h3>
                                <div class="mt-4 space-y-3 max-h-96 overflow-y-auto">
                                    <template x-for="result in youtubeResults" :key="result.id.videoId">
                                        <div class="p-3 hover:bg-gray-100 rounded-lg cursor-pointer flex items-start space-x-4" @click="selectVideo(result)">
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
                        <button type="button" @click="closeModal" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Fechar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('trackManager', () => ({
            tracks: @json($vinyl->tracks ?? []),
            showModal: false,
            youtubeResults: [],
            activeTrackIndex: null,
            isLoading: false,

            addTrack() {
                this.tracks.push({ id: null, name: '', duration: '', youtube_url: '' });
            },

            removeTrack(index) {
                this.tracks.splice(index, 1);
            },

            async searchYouTube(index) {
                this.activeTrackIndex = index;
                this.isLoading = true;
                const trackName = this.tracks[index].name;
                const artistName = '{{ $vinyl->artists->pluck('name')->join(" ") }}';
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
                    this.showModal = true;
                } catch (error) {
                    console.error('Erro ao pesquisar no YouTube:', error);
                    Toastify({
                        text: "Erro ao pesquisar no YouTube. Tente novamente.",
                        duration: 3000,
                        gravity: "top",
                        position: "right",
                        backgroundColor: "linear-gradient(to right, #FF5F6D, #FFC371)"
                    }).showToast();
                } finally {
                    this.isLoading = false;
                }
            },

            selectVideo(video) {
                if (this.activeTrackIndex !== null) {
                    this.tracks[this.activeTrackIndex].youtube_url = `https://www.youtube.com/watch?v=${video.id.videoId}`;
                }
                this.closeModal();
            },

            closeModal() {
                this.showModal = false;
                this.youtubeResults = [];
                this.activeTrackIndex = null;
            }
        }));
    });
    </script>
</x-admin-layout>





