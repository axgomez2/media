<x-admin-layout title="Editar Disco">
    <div class="p-4" x-data="{ isPresale: {{ old('is_presale', $vinyl->vinylSec->is_presale ?? false) ? 'true' : 'false' }} }">
        
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">✏️ {{ $vinyl->title }}</h1>
                <p class="text-sm text-gray-500 mt-1">ID: #{{ $vinyl->id }} | Atualizado: {{ $vinyl->updated_at->format('d/m/Y H:i') }}</p>
            </div>
            <a href="{{ route('admin.vinyls.index') }}" class="text-white bg-gray-700 hover:bg-gray-800 font-medium rounded-lg text-sm px-5 py-2.5">← Voltar</a>
        </div>

        <form action="{{ route('admin.vinyls.update', $vinyl->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- Coluna Esquerda -->
                <div class="lg:col-span-2 space-y-6">
                    
                    <!-- Descrição -->
                    <div class="p-6 bg-white border rounded-lg shadow-sm">
                        <h2 class="text-lg font-semibold mb-4 pb-3 border-b">📝 Descrição</h2>
                        <textarea name="description" rows="4" class="block p-2.5 w-full text-sm bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500">{{ old('description', $vinyl->description) }}</textarea>
                    </div>

                    <!-- Categorias -->
                    <div class="p-6 bg-white border rounded-lg shadow-sm">
                        <h2 class="text-lg font-semibold mb-4 pb-3 border-b">🎵 Categorias</h2>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                            @php $selectedCats = old('category_ids', $vinyl->categories->pluck('id')->toArray()); @endphp
                            @foreach($categories as $cat)
                                <label class="flex items-center p-2 rounded hover:bg-gray-50 cursor-pointer">
                                    <input type="checkbox" name="category_ids[]" value="{{ $cat->id }}" class="w-4 h-4 text-blue-600 rounded" {{ in_array($cat->id, $selectedCats) ? 'checked' : '' }}>
                                    <span class="ml-2 text-sm">{{ $cat->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Códigos -->
                    <div class="p-6 bg-white border rounded-lg shadow-sm">
                        <h2 class="text-lg font-semibold mb-4 pb-3 border-b">🔢 Códigos</h2>
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label class="block mb-2 text-sm font-medium">Catálogo</label>
                                <input type="text" name="catalog_number" value="{{ old('catalog_number', $vinyl->vinylSec->catalog_number) }}" class="bg-gray-50 border border-gray-300 text-sm rounded-lg block w-full p-2.5">
                            </div>
                            <div>
                                <label class="block mb-2 text-sm font-medium">Código de Barras</label>
                                <input type="text" name="barcode" value="{{ old('barcode', $vinyl->vinylSec->barcode) }}" class="bg-gray-50 border border-gray-300 text-sm rounded-lg block w-full p-2.5">
                            </div>
                            <div>
                                <label class="block mb-2 text-sm font-medium">Código Interno</label>
                                <input type="text" name="internal_code" value="{{ old('internal_code', $vinyl->vinylSec->internal_code) }}" class="bg-gray-50 border border-gray-300 text-sm rounded-lg block w-full p-2.5">
                            </div>
                        </div>
                    </div>

                    <!-- Atributos Físicos -->
                    <div class="p-6 bg-white border rounded-lg shadow-sm">
                        <h2 class="text-lg font-semibold mb-4 pb-3 border-b">📀 Atributos Físicos</h2>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block mb-2 text-sm font-medium">Formato</label>
                                <select name="format" class="bg-gray-50 border text-sm rounded-lg block w-full p-2.5">
                                    <option value="">Selecione...</option>
                                    <option value="LP" {{ old('format', $vinyl->vinylSec->format) == 'LP' ? 'selected' : '' }}>LP (12")</option>
                                    <option value="Single" {{ old('format', $vinyl->vinylSec->format) == 'Single' ? 'selected' : '' }}>Single (7")</option>
                                    <option value="EP" {{ old('format', $vinyl->vinylSec->format) == 'EP' ? 'selected' : '' }}>EP</option>
                                </select>
                            </div>
                            <div>
                                <label class="block mb-2 text-sm font-medium">Nº Discos</label>
                                <input type="number" name="num_discs" value="{{ old('num_discs', $vinyl->vinylSec->num_discs ?? 1) }}" min="1" class="bg-gray-50 border text-sm rounded-lg block w-full p-2.5">
                            </div>
                            <div>
                                <label class="block mb-2 text-sm font-medium">Velocidade (RPM)</label>
                                <select name="speed" class="bg-gray-50 border text-sm rounded-lg block w-full p-2.5">
                                    <option value="">Selecione...</option>
                                    <option value="33 1/3" {{ old('speed', $vinyl->vinylSec->speed) == '33 1/3' ? 'selected' : '' }}>33 1/3</option>
                                    <option value="45" {{ old('speed', $vinyl->vinylSec->speed) == '45' ? 'selected' : '' }}>45</option>
                                    <option value="78" {{ old('speed', $vinyl->vinylSec->speed) == '78' ? 'selected' : '' }}>78</option>
                                </select>
                            </div>
                            <div>
                                <label class="block mb-2 text-sm font-medium">Edição</label>
                                <input type="text" name="edition" value="{{ old('edition', $vinyl->vinylSec->edition) }}" placeholder="Ex: Limitada" class="bg-gray-50 border text-sm rounded-lg block w-full p-2.5">
                            </div>
                            <div>
                                <label class="block mb-2 text-sm font-medium">Peso *</label>
                                <select name="weight_id" class="bg-gray-50 border text-sm rounded-lg block w-full p-2.5" required>
                                    @foreach($weights as $w)
                                        <option value="{{ $w->id }}" {{ old('weight_id', $vinyl->vinylSec->weight_id) == $w->id ? 'selected' : '' }}>{{ $w->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block mb-2 text-sm font-medium">Dimensão *</label>
                                <select name="dimension_id" class="bg-gray-50 border text-sm rounded-lg block w-full p-2.5" required>
                                    @foreach($dimensions as $d)
                                        <option value="{{ $d->id }}" {{ old('dimension_id', $vinyl->vinylSec->dimension_id) == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Condição -->
                    <div class="p-6 bg-white border rounded-lg shadow-sm">
                        <h2 class="text-lg font-semibold mb-4 pb-3 border-b">⭐ Condição</h2>
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block mb-2 text-sm font-medium">Estado da Mídia</label>
                                <select name="midia_status_id" class="bg-gray-50 border text-sm rounded-lg block w-full p-2.5">
                                    <option value="">Selecione...</option>
                                    @if(isset($midiaStatuses))
                                        @foreach($midiaStatuses as $status)
                                            <option value="{{ $status->id }}" {{ old('midia_status_id', $vinyl->vinylSec->midia_status_id) == $status->id ? 'selected' : '' }}>{{ $status->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div>
                                <label class="block mb-2 text-sm font-medium">Estado da Capa</label>
                                <select name="cover_status_id" class="bg-gray-50 border text-sm rounded-lg block w-full p-2.5">
                                    <option value="">Selecione...</option>
                                    @if(isset($coverStatuses))
                                        @foreach($coverStatuses as $status)
                                            <option value="{{ $status->id }}" {{ old('cover_status_id', $vinyl->vinylSec->cover_status_id) == $status->id ? 'selected' : '' }}>{{ $status->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-medium">Observações</label>
                            <textarea name="notes" rows="3" placeholder="Detalhes sobre a condição..." class="block p-2.5 w-full text-sm bg-gray-50 rounded-lg border">{{ old('notes', $vinyl->vinylSec->notes) }}</textarea>
                        </div>
                    </div>

                    <!-- Fornecedor -->
                    @if(isset($suppliers))
                    <div class="p-6 bg-white border rounded-lg shadow-sm">
                        <h2 class="text-lg font-semibold mb-4 pb-3 border-b">🏢 Fornecedor</h2>
                        <select name="supplier_id" class="bg-gray-50 border text-sm rounded-lg block w-full p-2.5">
                            <option value="">Nenhum</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" {{ old('supplier_id', $vinyl->vinylSec->supplier_id) == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                </div>

                <!-- Coluna Direita -->
                <div class="space-y-6">
                    
                    <!-- Preço e Estoque -->
                    <div class="p-6 bg-white border rounded-lg shadow-sm">
                        <h2 class="text-lg font-semibold mb-4 pb-3 border-b">💰 Preço e Estoque</h2>
                        <div class="space-y-4">
                            <div>
                                <label class="block mb-2 text-sm font-medium">Preço de Venda (R$) *</label>
                                <input type="text" name="price" value="{{ old('price', $vinyl->vinylSec->price ?? '0.00') }}" class="money bg-gray-50 border text-sm rounded-lg block w-full p-2.5" required>
                            </div>
                            <div>
                                <label class="block mb-2 text-sm font-medium">Preço de Compra (R$)</label>
                                <input type="text" name="buy_price" value="{{ old('buy_price', $vinyl->vinylSec->buy_price) }}" class="money bg-gray-50 border text-sm rounded-lg block w-full p-2.5">
                                <p class="text-xs text-gray-500 mt-1">Custo de aquisição</p>
                            </div>
                            <div>
                                <label class="block mb-2 text-sm font-medium">Preço Promocional (R$)</label>
                                <input type="text" name="promotional_price" value="{{ old('promotional_price', $vinyl->vinylSec->promotional_price) }}" class="money bg-gray-50 border text-sm rounded-lg block w-full p-2.5">
                            </div>
                            <div>
                                <label class="block mb-2 text-sm font-medium">Estoque *</label>
                                <input type="number" name="stock" value="{{ old('stock', $vinyl->vinylSec->stock ?? 0) }}" min="0" class="bg-gray-50 border text-sm rounded-lg block w-full p-2.5" required>
                            </div>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="p-6 bg-white border rounded-lg shadow-sm">
                        <h2 class="text-lg font-semibold mb-4 pb-3 border-b">🏷️ Status</h2>
                        <div class="space-y-3">
                            <label class="flex items-center p-3 bg-gray-50 rounded cursor-pointer hover:bg-gray-100">
                                <input type="checkbox" name="is_new" value="1" {{ old('is_new', $vinyl->vinylSec->is_new ?? true) ? 'checked' : '' }} class="w-5 h-5 text-green-600 rounded">
                                <span class="ml-3 text-sm font-medium">🆕 Disco Novo</span>
                            </label>
                            <label class="flex items-center p-3 bg-gray-50 rounded cursor-pointer hover:bg-gray-100">
                                <input type="checkbox" name="is_promotional" value="1" {{ old('is_promotional', $vinyl->vinylSec->is_promotional) ? 'checked' : '' }} class="w-5 h-5 text-yellow-600 rounded">
                                <span class="ml-3 text-sm font-medium">🔥 Em Promoção</span>
                            </label>
                            <label class="flex items-center p-3 bg-gray-50 rounded cursor-pointer hover:bg-gray-100">
                                <input type="checkbox" name="is_presale" x-model="isPresale" value="1" {{ old('is_presale', $vinyl->vinylSec->is_presale) ? 'checked' : '' }} class="w-5 h-5 text-purple-600 rounded">
                                <span class="ml-3 text-sm font-medium">🔜 Pré-venda</span>
                            </label>
                            <label class="flex items-center p-3 bg-gray-50 rounded cursor-pointer hover:bg-gray-100">
                                <input type="checkbox" name="in_stock" value="1" {{ old('in_stock', $vinyl->vinylSec->in_stock) ? 'checked' : '' }} class="w-5 h-5 text-blue-600 rounded">
                                <span class="ml-3 text-sm font-medium">✅ Disponível</span>
                            </label>
                        </div>
                    </div>

                    <!-- Data de Pré-venda -->
                    <div x-show="isPresale" x-transition class="p-6 bg-purple-50 border border-purple-200 rounded-lg shadow-sm">
                        <h2 class="text-lg font-semibold mb-4 pb-3 border-b border-purple-300 text-purple-900">📅 Previsão de Chegada</h2>
                        <div>
                            <label class="block mb-2 text-sm font-medium text-purple-900">Data Prevista</label>
                            <input type="date" name="presale_arrival_date" value="{{ old('presale_arrival_date', $vinyl->vinylSec->presale_arrival_date) }}" class="bg-white border border-purple-300 text-sm rounded-lg block w-full p-2.5 focus:ring-purple-500 focus:border-purple-500">
                            <p class="text-xs text-purple-600 mt-1">Quando o produto chegará ao estoque</p>
                        </div>
                    </div>

                    <!-- Botão Salvar -->
                    <button type="submit" class="w-full text-white bg-emerald-600 hover:bg-emerald-700 font-medium rounded-lg text-sm px-5 py-3 shadow-lg">
                        💾 Salvar Alterações
                    </button>

                </div>
            </div>
        </form>

    </div>
</x-admin-layout>
