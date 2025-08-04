@props([
    'title',
    'value',
    'icon' => null,
    'iconColor' => 'indigo',
    'trend' => null, // 'up', 'down', 'neutral'
    'trendValue' => null,
    'subtitle' => null
])

@php
    $iconColorClasses = [
        'indigo' => 'text-indigo-500 bg-indigo-100',
        'green' => 'text-green-500 bg-green-100',
        'blue' => 'text-blue-500 bg-blue-100',
        'purple' => 'text-purple-500 bg-purple-100',
        'yellow' => 'text-yellow-500 bg-yellow-100',
        'orange' => 'text-orange-500 bg-orange-100',
        'red' => 'text-red-500 bg-red-100',
        'gray' => 'text-gray-500 bg-gray-100'
    ];

    $trendClasses = [
        'up' => 'text-green-600',
        'down' => 'text-red-600',
        'neutral' => 'text-gray-600'
    ];

    $trendIcons = [
        'up' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>',
        'down' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path>',
        'neutral' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>'
    ];

    $iconColorClass = $iconColorClasses[$iconColor] ?? $iconColorClasses['indigo'];
@endphp

<div {{ $attributes->merge(['class' => 'flex items-center p-4 bg-white rounded-lg shadow-xs']) }}>
    @if($icon)
        <div class="p-3 mr-4 {{ $iconColorClass }} rounded-full">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                {!! $icon !!}
            </svg>
        </div>
    @endif

    <div class="flex-1">
        <p class="mb-2 text-sm font-medium text-gray-600">
            {{ $title }}
        </p>
        <div class="flex items-center space-x-2">
            <p class="text-lg font-semibold text-gray-700">
                {{ $value }}
            </p>

            @if($trend && $trendValue)
                <div class="flex items-center {{ $trendClasses[$trend] ?? $trendClasses['neutral'] }}">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        {!! $trendIcons[$trend] ?? $trendIcons['neutral'] !!}
                    </svg>
                    <span class="text-xs font-medium">{{ $trendValue }}</span>
                </div>
            @endif
        </div>

        @if($subtitle)
            <p class="text-xs text-gray-500 mt-1">
                {{ $subtitle }}
            </p>
        @endif
    </div>
</div>
