@props([
    'address',
    'showDefault' => true,
    'compact' => false
])

@php
    $isDefault = $address->is_default ?? false;
    $containerClass = $compact ? 'p-2' : 'p-4';
    $borderClass = $isDefault ? 'bg-indigo-50 border-indigo-200' : 'bg-gray-50';
@endphp

<div {{ $attributes->merge(['class' => "{$containerClass} border border-gray-200 rounded-lg {$borderClass}"]) }}>
    @if($showDefault && $isDefault)
        <div class="flex items-center mb-2">
            <svg class="w-4 h-4 text-indigo-500 mr-1" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"></path>
            </svg>
            <span class="text-xs font-medium text-indigo-700">Endereço Principal</span>
        </div>
    @endif

    <div class="{{ $compact ? 'text-xs' : 'text-sm' }} text-gray-900">
        <p class="font-medium">
            {{ $address->street }}{{ $address->number ? ', ' . $address->number : '' }}
        </p>

        @if($address->complement)
            <p class="text-gray-600">{{ $address->complement }}</p>
        @endif

        <p class="text-gray-600">{{ $address->neighborhood }}</p>
        <p class="text-gray-600">{{ $address->city }} - {{ $address->state }}</p>

        @if($address->zip_code)
            <p class="text-gray-600">
                CEP: {{ $address->formatted_zip_code ?? preg_replace('/(\d{5})(\d{3})/', '$1-$2', $address->zip_code) }}
            </p>
        @endif
    </div>

    @if(!$compact && $address->created_at)
        <div class="mt-2 pt-2 border-t border-gray-200">
            <p class="text-xs text-gray-500">
                Cadastrado em {{ $address->created_at->format('d/m/Y') }}
            </p>
        </div>
    @endif
</div>
