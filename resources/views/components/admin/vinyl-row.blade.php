<tr class="bg-white border-b hover:bg-gray-50">
    <td class="p-4 w-32">
        <img class="w-16 h-16 rounded-lg object-cover"
             src="{{ $vinyl->cover_image ? route('media.show', ['path' => $vinyl->cover_image]) : asset('images/borken.png') }}">
    </td>
    <td class="px-2 py-4">
        <div class="font-semibold text-gray-900">
            {{ Str::limit($vinyl->artists->pluck('name')->join(', '), 24) }}
        </div>
        <div class="text-sm text-gray-500">{{ Str::limit($vinyl->title, 24) }}</div>
        @php
            $totalTracks = $vinyl->tracks->count();
            $tracksWithYoutube = $vinyl->tracks->whereNotNull('youtube_url')->count();
            $allTracksHaveYoutube = $totalTracks > 0 && $totalTracks === $tracksWithYoutube;
        @endphp
            <div class="text-sm {{ $allTracksHaveYoutube ? 'text-green-800' : 'text-red-800' }} text-xs font-medium   mt-2">
                Faixas: {{ $totalTracks }} ({{ $tracksWithYoutube }} com YouTube)
            </div>

    </td>
    <td class="px-6 py-4 font-medium text-gray-900">R$ {{ $vinyl->vinylSec->price ?? '--' }}</td>
    <td class="px-6 py-4 font-medium text-gray-900">R$ {{ $vinyl->vinylSec->promotional_price ?? '--' }}</td>
    <td class="px-6 py-4">{{ $vinyl->release_year }}</td>
    <td class="px-6 py-4">{{ $vinyl->vinylSec->stock ?? '0' }}</td>
    <td class="px-6 py-4">
        <div class="space-y-3">
            <!-- Toggle Promoção -->
            <div class="flex items-center">
                <div class="relative inline-flex items-center">
                    <form action="{{ route('admin.vinyls.updateField') }}" method="POST">
                        @csrf
                        <input type="hidden" name="id" value="{{ $vinyl->id }}">
                        <input type="hidden" name="field" value="is_promotional">
                        <input type="hidden" name="value" value="{{ $vinyl->vinylSec && $vinyl->vinylSec->is_promotional ? 0 : 1 }}">
                        <button type="submit"
                                class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-0 transition-colors duration-200 ease-in-out focus:outline-none focus:ring-4 focus:ring-blue-300 {{ $vinyl->vinylSec && $vinyl->vinylSec->is_promotional ? 'bg-blue-600' : 'bg-gray-200' }}">
                            <span class="sr-only">Toggle Promotional</span>
                            <span class="absolute top-[2px] left-[2px] h-5 w-5 rounded-full bg-white border border-gray-300 transition-all transform {{ $vinyl->vinylSec && $vinyl->vinylSec->is_promotional ? 'translate-x-5' : 'translate-x-0' }}"></span>
                        </button>
                    </form>
                </div>
                <span class="ml-3 text-sm font-medium text-gray-900">Em promoção</span>
            </div>

            <!-- Toggle Estoque -->
            <div class="flex items-center">
                <div class="relative inline-flex items-center">
                    <form action="{{ route('admin.vinyls.updateField') }}" method="POST">
                        @csrf
                        <input type="hidden" name="id" value="{{ $vinyl->id }}">
                        <input type="hidden" name="field" value="in_stock">
                        <input type="hidden" name="value" value="{{ $vinyl->vinylSec && $vinyl->vinylSec->in_stock ? 0 : 1 }}">
                        <button type="submit"
                                class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-0 transition-colors duration-200 ease-in-out focus:outline-none focus:ring-4 focus:ring-blue-300 {{ $vinyl->vinylSec && $vinyl->vinylSec->in_stock ? 'bg-blue-600' : 'bg-gray-200' }}">
                            <span class="sr-only">Toggle Stock</span>
                            <span class="absolute top-[2px] left-[2px] h-5 w-5 rounded-full bg-white border border-gray-300 transition-all transform {{ $vinyl->vinylSec && $vinyl->vinylSec->in_stock ? 'translate-x-5' : 'translate-x-0' }}"></span>
                        </button>
                    </form>
                </div>
                <span class="ml-3 text-sm font-medium text-gray-900">Em estoque</span>
            </div>
        </div>
    </td>
    <td class="px-6 py-4">

<button type="button" data-modal-target="crypto-modal-{{ $vinyl->id }}" data-modal-toggle="crypto-modal-{{ $vinyl->id }}" class="text-gray-900 bg-sky-500 hover:bg-sky-600 border border-sky-500 focus:ring-4 focus:outline-none focus:ring-gray-100 font-medium rounded-lg text-sm px-5 py-2.5 text-center inline-flex items-center">
    <svg aria-hidden="true" class="w-4 h-4 me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
   Clique para abrir
    </button>

    <!-- Main modal -->
    <div id="crypto-modal-{{ $vinyl->id }}" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div class="relative p-4 w-full max-w-md max-h-full">
            <!-- Modal content -->
            <div class="relative bg-white rounded-lg shadow-sm">
                <!-- Modal header -->
                <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">
                       Central de ações:
                    </h3>
                    <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm h-8 w-8 ms-auto inline-flex justify-center items-center" data-modal-toggle="crypto-modal-{{ $vinyl->id }}">
                        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                        </svg>
                        <span class="sr-only">Close modal</span>
                    </button>
                </div>
                <!-- Modal body -->
                <div class="p-4 md:p-5">
                    <p class="text-sm font-normal text-gray-700 ">O que você deseja fazer no disco: </p>
                    <p class="pt-2" ><strong>{{ $vinyl->artists->pluck('name')->join(', ') }} - {{ $vinyl->title }}</strong>?</p>
                    <ul class="my-4 space-y-3">
                        <li>
                            <a href="{{ route('admin.vinyls.edit', $vinyl->id) }}" class="flex items-center p-3 text-base font-bold text-gray-900 rounded-lg bg-yellow-400 hover:bg-yellow-500 group hover:shadow ">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>                                <span class="flex-1 ms-3 whitespace-nowrap">Editar cadastro ?</span>
                            </a>
                        </li>


                        <li>
                            <a href="{{ route('admin.vinyls.edit-tracks', $vinyl->id) }}" class="flex items-center p-3 text-base font-bold text-gray-900 rounded-lg bg-sky-400 hover:bg-sky-500 group hover:shadow ">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                                </svg>                                <span class="flex-1 ms-3 whitespace-nowrap">Gerenciar Faixas do Disco ?</span>
                            </a>
                        </li>

                        @if($vinyl->vinylSec)
                        <li>
                            <a href="{{ route('admin.vinyl.images', $vinyl->id) }}" class="flex items-center p-3 text-base font-bold text-gray-900 rounded-lg bg-purple-400 hover:bg-purple-500 group hover:shadow ">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span class="flex-1 ms-3 whitespace-nowrap">Gerenciar Imagens do disco ?</span>
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('admin.vinyls.show', $vinyl->id) }}" class="flex items-center p-3 text-base font-bold text-gray-900 rounded-lg bg-emerald-400 hover:bg-emerald-500 group hover:shadow ">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <span class="flex-1 ms-3 whitespace-nowrap">Visualisar dados do disco ?</span>
                            </a>
                        </li>
                        @else

                        <li>
                            <a href="{{ route('admin.vinyls.complete', $vinyl->id) }}" class="flex items-center p-3 text-base font-bold text-gray-900 rounded-lg bg-gray-50 hover:bg-gray-100 group hover:shadow ">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                <span class="flex-1 ms-3 whitespace-nowrap">Completar o cadastro do disco</span>
                            </a>
                        </li>

                        @endif


                        <li>
                            <form action="{{ route('admin.vinyls.destroy', $vinyl->id) }}"
                                method="POST"
                                class="flex items-center p-3 text-base font-bold text-gray-900 rounded-lg bg-red-500 hover:bg-red-600 group hover:shadow"
                                x-data
                                @submit.prevent="if (confirm('Tem certeza que deseja excluir esse disco?')) $el.submit()">
                              @csrf
                              @method('DELETE')
                              <button type="submit"
                                      class="flex items-center ">
                                  <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                  </svg>
                                  Excluir o disco
                              </button>
                          </form>
                        </li>

                    </ul>
                    <div>
                        <a href="{{ route('admin.vinyls.index') }}" class="inline-flex items-center text-xs font-normal text-gray-500 hover:underline ">
                            <svg class="w-3 h-3 me-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7.529 7.988a2.502 2.502 0 0 1 5 .191A2.441 2.441 0 0 1 10 10.582V12m-.01 3.008H10M19 10a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                            </svg>
                            recarregar a pagina?</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    </td>
</tr>
