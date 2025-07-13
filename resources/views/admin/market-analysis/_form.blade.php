@if ($errors->any())
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
        <p class="font-bold">Ocorreram erros:</p>
        <ul class="list-disc ml-5 mt-2">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="grid grid-cols-1 md:grid-cols-12 gap-6">
    <!-- Data da Análise -->
    <div class="md:col-span-4">
        <label for="analysis_date" class="block text-sm font-medium text-gray-700">Data da Análise</label>
        <input type="date" name="analysis_date" id="analysis_date" value="{{ old('analysis_date', $analysis->analysis_date ? Carbon\Carbon::parse($analysis->analysis_date)->format('Y-m-d') : '') }}" required
               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
    </div>

    <!-- Total de Listings -->
    <div class="md:col-span-8">
        <label for="total_listings" class="block text-sm font-medium text-gray-700">Total de Listings (Mundo)</label>
        <input type="number" name="total_listings" id="total_listings" placeholder="Ex: 50123456" value="{{ old('total_listings', $analysis->total_listings) }}" required
               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
    </div>
</div>

<div class="my-6 border-t border-gray-200"></div>

<h3 class="text-lg font-medium text-gray-900 mb-4">Listings por País</h3>

<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-6">
    @php
        $countries = [
            'br_listings' => 'Brasil',
            'us_listings' => 'EUA',
            'gb_listings' => 'Reino Unido',
            'de_listings' => 'Alemanha',
            'fr_listings' => 'França',
            'it_listings' => 'Itália',
            'jp_listings' => 'Japão',
            'ca_listings' => 'Canadá',
            'be_listings' => 'Bélgica',
            'se_listings' => 'Suécia',
        ];
    @endphp

    @foreach ($countries as $field => $label)
    <div>
        <label for="{{ $field }}" class="block text-sm font-medium text-gray-700">{{ $label }}</label>
        <input type="number" name="{{ $field }}" id="{{ $field }}" value="{{ old($field, $analysis->$field) }}" placeholder="0"
               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
    </div>
    @endforeach
</div>
