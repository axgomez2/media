@props([
    'type' => 'default', // verified, unverified, active, inactive, abandoned-cart, recent-activity, no-activity
    'text' => '',
    'icon' => null,
    'size' => 'sm' // xs, sm, md
])

@php
    $baseClasses = 'inline-flex items-center font-medium rounded-full';

    $sizeClasses = [
        'xs' => 'px-2 py-0.5 text-xs',
        'sm' => 'px-2.5 py-0.5 text-xs',
        'md' => 'px-3 py-1 text-sm'
    ];

    $iconSizeClasses = [
        'xs' => 'w-3 h-3',
        'sm' => 'w-4 h-4',
        'md' => 'w-4 h-4'
    ];

    $typeStyles = [
        'verified' => 'text-green-700 bg-green-100',
        'unverified' => 'text-yellow-700 bg-yellow-100',
        'active' => 'text-green-700 bg-green-100',
        'inactive' => 'text-red-700 bg-red-100',
        'abandoned-cart' => 'text-orange-700 bg-orange-100',
        'recent-activity' => 'text-blue-700 bg-blue-100',
        'no-activity' => 'text-gray-700 bg-gray-100',
        'default' => 'text-gray-700 bg-gray-100'
    ];

    $typeIcons = [
        'verified' => '<path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>',
        'unverified' => '<path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>',
        'active' => '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>',
        'inactive' => '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>',
        'abandoned-cart' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m6-5v6a2 2 0 01-2 2H9a2 2 0 01-2-2v-6m8 0V9a2 2 0 00-2-2H9a2 2 0 00-2 2v4.01"></path>',
        'recent-activity' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.636 18.364a9 9 0 010-12.728m12.728 0a9 9 0 010 12.728m-9.9-2.829a5 5 0 010-7.07m7.072 0a5 5 0 010 7.07M13 12a1 1 0 11-2 0 1 1 0 012 0z"></path>',
        'no-activity' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728"></path>'
    ];

    $sizeClass = $sizeClasses[$size] ?? $sizeClasses['sm'];
    $iconSizeClass = $iconSizeClasses[$size] ?? $iconSizeClasses['sm'];
    $typeStyle = $typeStyles[$type] ?? $typeStyles['default'];
    $typeIcon = $icon ?? ($typeIcons[$type] ?? null);

    $classes = "{$baseClasses} {$sizeClass} {$typeStyle}";
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    @if($typeIcon)
        <svg class="{{ $iconSizeClass }} inline mr-1"
             fill="{{ in_array($type, ['abandoned-cart', 'recent-activity', 'no-activity']) ? 'none' : 'currentColor' }}"
             stroke="{{ in_array($type, ['abandoned-cart', 'recent-activity', 'no-activity']) ? 'currentColor' : 'none' }}"
             viewBox="0 0 20 20">
            {!! $typeIcon !!}
        </svg>
    @endif
    {{ $text }}
</span>
