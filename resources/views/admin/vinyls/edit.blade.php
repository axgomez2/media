<x-admin-layout title="Editar Disco">
    <div class="p-4" x-data="{ showDiscogsModal: false, showUploadModal: false }">
        <div class="flex justify-between items-center mb-6">
                    <div>
                <h1 class="text-2xl font-semibold text-gray-900 sm:text-3xl">Editar Disco</h1>
                <p class="text-sm text-gray-500 mt-1">Gerencie os detalhes do disco: {{ $vinyl->title }}</p>
            </div>
            <a href="{{ route('admin.vinyls.index') }}" class="text-white bg-gray-700 hover:bg-gray-800 focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2.5 transition-colors">
                Voltar para Início
            </a>
        </div>

        <form action="{{ route('admin.vinyls.update', $vinyl->id) }}" method="POST" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            @csrf
            @method('PUT')

            <!-- Coluna Principal (Esquerda) -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Card de Informações Principais -->
                <div class="p-6 bg-white border border-gray-200 rounded-lg shadow-sm">
                    <h2 class="text-xl font-semibold mb-4 text-gray-900 border-b pb-3">Informações Principais</h2>

                    <!-- Descrição -->
                <div>
                    <label for="description" class="block mb-2 text-sm font-medium text-gray-900">Descrição</label>
                        <textarea id="description" name="description" rows="6" class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-primary-500 focus:border-primary-500">{{ old('description', $vinyl->description) }}</textarea>
                </div>
                </div>

                <!-- Card de Categorias -->
                <div class="p-6 bg-white border border-gray-200 rounded-lg shadow-sm">
                    <h2 class="text-xl font-semibold mb-4 text-gray-900 border-b pb-3">Categorias</h2>
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mt-4">
                        @php
                            $selectedCategories = old('category_ids', $vinyl->categories->pluck('id')->toArray());
                        @endphp

                        @foreach($categories as $category)
                            <div class="flex items-center">
                                <input id="category_{{ $category->id }}" type="checkbox" name="category_ids[]" value="{{ $category->id }}" class="w-4 h-4 text-primary-600 bg-gray-100 border-gray-300 rounded focus:ring-primary-500" {{ in_array($category->id, $selectedCategories) ? 'checked' : '' }}>
                                <label for="category_{{ $category->id }}" class="ml-2 text-sm font-medium text-gray-900">{{ $category->name }}</label>
                            </div>
                        @endforeach
                    </div>
                    @error('category_ids')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Coluna Lateral (Direita) -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Card de Imagem -->
                <div class="p-6 bg-white border border-gray-200 rounded-lg shadow-sm">
                    <h2 class="text-xl font-semibold mb-4 text-gray-900 border-b pb-3">Imagem do Disco</h2>

                    <!-- Imagem Atual -->
                    <div class="mb-4">
                        @if($vinyl->cover_image)
                            <img src="{{ Storage::url($vinyl->cover_image) }}" alt="{{ $vinyl->title }}" class="w-full h-auto object-cover rounded-lg shadow">
                        @else
                            <div class="w-full aspect-square bg-gray-100 flex items-center justify-center rounded-lg">
                                <span class="text-gray-500">Sem imagem</span>
                            </div>
                        @endif
                    </div>

                    <!-- Botões de Ação da Imagem -->
                    <div class="flex flex-col space-y-2">
                        <button type="button" @click="showDiscogsModal = true" class="w-full text-center text-gray-900 bg-white border border-gray-300 focus:outline-none hover:bg-gray-100 focus:ring-4 focus:ring-gray-200 font-medium rounded-lg text-sm px-5 py-2.5 transition-colors">
                            Buscar no Discogs
                        </button>
                        <button type="button" @click="showUploadModal = true" class="w-full text-center text-white bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 transition-colors">
                            Upload Manual
                        </button>
                        @if($vinyl->cover_image)
                            <button type="button" onclick="document.getElementById('remove-image-form').submit();" class="w-full text-center text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 transition-colors">
                                Remover Imagem
                            </button>
                        @endif
                    </div>
                </div>

                <!-- Card de Preço e Estoque -->
                <div class="p-6 bg-white border border-gray-200 rounded-lg shadow-sm">
                    <h2 class="text-xl font-semibold mb-4 text-gray-900 border-b pb-3">Preço e Estoque</h2>
                    <div class="space-y-4 mt-4">
                        <div>
                            <label for="price" class="block mb-2 text-sm font-medium text-gray-900">Preço (R$)</label>
                            <input type="text" id="price" name="price" value="{{ old('price', $vinyl->vinylSec->price ?? '0.00') }}" class="money bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5" required>
                        </div>
                        <div>
                            <label for="promotional_price" class="block mb-2 text-sm font-medium text-gray-900">Preço Promocional (R$)</label>
                            <input type="text" id="promotional_price" name="promotional_price" value="{{ old('promotional_price', $vinyl->vinylSec->promotional_price ?? '') }}" class="money bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5">
                        </div>
                        <div>
                            <label for="stock" class="block mb-2 text-sm font-medium text-gray-900">Quantidade em Estoque</label>
                            <input type="text" id="stock" name="stock" value="{{ old('stock', $vinyl->vinylSec->stock ?? 0) }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5" required min="0">
                        </div>
                    </div>
                </div>

                <!-- Card de Status -->
                <div class="p-6 bg-white border border-gray-200 rounded-lg shadow-sm">
                     <h2 class="text-xl font-semibold mb-4 text-gray-900 border-b pb-3">Status</h2>
                    <div class="space-y-4 mt-4">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="is_promotional" class="sr-only peer" {{ (old('is_promotional', $vinyl->vinylSec->is_promotional ?? false)) ? 'checked' : '' }} value="1">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600"></div>
                            <span class="ml-3 text-sm font-medium text-gray-500 transition-colors duration-200 peer-checked:font-semibold peer-checked:text-primary-600">Em Promoção</span>
                        </label>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="in_stock" class="sr-only peer" {{ (old('in_stock', $vinyl->vinylSec->in_stock ?? false)) ? 'checked' : '' }} value="1">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600"></div>
                            <span class="ml-3 text-sm font-medium text-gray-500 transition-colors duration-200 peer-checked:font-semibold peer-checked:text-primary-600">Disponível para Venda</span>
                        </label>
                    </div>
                </div>

                <!-- Card de Atributos -->
                <div class="p-6 bg-white border border-gray-200 rounded-lg shadow-sm">
                    <h2 class="text-xl font-semibold mb-4 text-gray-900 border-b pb-3">Atributos Físicos</h2>
                    <div class="space-y-4 mt-4">
                        <div>
                            <label for="weight_id" class="block mb-2 text-sm font-medium text-gray-900">Peso</label>
                            <select id="weight_id" name="weight_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5" required>
                                @foreach($weights as $weight)
                                    <option value="{{ $weight->id }}" {{ (old('weight_id', $vinyl->vinylSec->weight_id ?? '') == $weight->id) ? 'selected' : '' }}>{{ $weight->name }}</option>
                                @endforeach
                            </select>
                        </div>
                <div>
                    <label for="dimension_id" class="block mb-2 text-sm font-medium text-gray-900">Dimensão</label>
                    <select id="dimension_id" name="dimension_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5" required>
                        @foreach($dimensions as $dimension)
                                    <option value="{{ $dimension->id }}" {{ (old('dimension_id', $vinyl->vinylSec->dimension_id ?? '') == $dimension->id) ? 'selected' : '' }}>{{ $dimension->name }}</option>
                        @endforeach
                    </select>
                </div>
                    </div>
                </div>

                <!-- Botão de Submissão -->
                <div class="sticky bottom-4">
                    <button type="submit" class="w-full text-white bg-emerald-600 hover:bg-emerald-700 focus:ring-4 focus:ring-emerald-300 font-medium rounded-lg text-sm px-5 py-3 text-center shadow-lg transition-colors">
                        Salvar Alterações
                    </button>
                </div>
            </div>
        </form>

        <!-- Formulário para Remover Imagem (separado) -->
        <form id="remove-image-form" action="{{ route('admin.vinyls.remove-image', $vinyl->id) }}" method="POST" class="hidden" onsubmit="return confirm('Tem certeza que deseja remover esta imagem?');">
            @csrf
            @method('DELETE')
        </form>

        <!-- Modais -->
        <!-- Modal Discogs -->
        <div x-show="showDiscogsModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 text-center">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showDiscogsModal = false"></div>
                <div class="inline-block bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-lg sm:w-full">
                    <div class="bg-white px-6 py-4">
                        <h3 class="text-lg font-medium leading-6 text-gray-900">Buscar Imagem no Discogs</h3>
                        <p class="mt-2 text-sm text-gray-500">Isto tentará encontrar e salvar a imagem principal do Discogs para este disco. Deseja continuar?</p>
                    </div>
                    <div class="bg-gray-50 px-6 py-3 flex flex-row-reverse">
                        <form action="{{ route('admin.vinyls.fetch-discogs-image', $vinyl->id) }}" method="POST" class="ml-3">
                            @csrf
                            <button type="submit" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-primary-600 text-base font-medium text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                Buscar
                            </button>
                        </form>
                        <button type="button" @click="showDiscogsModal = false" class="w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
                </div>

        <!-- Modal Upload Manual -->
        <div x-show="showUploadModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 text-center">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showUploadModal = false"></div>
                <div class="inline-block bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-lg sm:w-full">
                    <form action="{{ route('admin.vinyls.upload-image', $vinyl->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="bg-white px-6 py-4">
                            <h3 class="text-lg font-medium leading-6 text-gray-900">Upload Manual de Imagem</h3>
                            <div class="mt-4">
                                <label class="block mb-2 text-sm font-medium text-gray-900" for="image_upload">Selecione uma imagem</label>
                                <input type="file" name="image" id="image_upload" accept="image/*" class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 file:mr-4 file:py-2 file:px-4 file:rounded-l-lg file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100" required>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-6 py-3 flex flex-row-reverse">
                            <button type="submit" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-primary-600 text-base font-medium text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 ml-3">
                                Enviar
                            </button>
                            <button type="button" @click="showUploadModal = false" class="w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                Cancelar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
    </div>
</div>
</x-admin-layout>
