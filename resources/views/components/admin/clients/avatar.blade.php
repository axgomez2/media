@props([
    'client',
    'size' => 'md', // sm, md, lg
    'showStatus' => true
])

@php
    $sizeClasses = [
        'sm' => 'w-8 h-8 text-sm',
        'md' => 'w-12 h-12 text-lg',
        'lg' => 'w-20 h-20 text-2xl'
    ];

    $statusSizeClasses = [
        'sm' => 'w-3 h-3',
        'md' => 'w-4 h-4',
        'lg' => 'w-6 h-6'
    ];

    $statusIconClasses = [
        'sm' => 'w-2 h-2',
        'md' => 'w-2.5 h-2.5',
        'lg' => 'w-3 h-3'
    ];

    $avatarClass = $sizeClasses[$size] ?? $sizeClasses['md'];
    $statusClass = $statusSizeClasses[$size] ?? $statusSizeClasses['md'];
    $statusIconClass = $statusIconClasses[$size] ?? $statusIconClasses['md'];

    // Generate initials from name
    $initials = collect(explode(' ', $client->name))
        ->map(fn($word) => strtoupper(substr($word, 0, 1)))
        ->take(2)
        ->join('');
@endphp

<div class="relative inline-block">
    @if($client->avatar && filter_var($client->avatar, FILTER_VALIDATE_URL))
        <img class="{{ $avatarClass }} rounded-full object-cover border-4 border-indigo-100"
             src="{{ $client->avatar }}"
             alt="{{ $client->name }}"
             loading="lazy">
    @else
        <div class="{{ $avatarClass }} rounded-full bg-indigo-500 border-4 border-indigo-100 flex items-center justify-center">
            <span class="font-bold text-white">{{ $initials }}</span>
        </div>
    @endif

    @if($showStatus)
        <!-- Email verification status indicator -->
        <div class="absolute -bottom-1 -right-1">
            @if($client->is_verified)
                <div class="{{ $statusClass }} bg-green-500 rounded-full border-2 border-white flex items-center justify-center"
                     title="Email verificado">
                    <svg class="{{ $statusIconClass }} text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                    </svg>
                </div>
            @else
                <div class="{{ $statusClass }} bg-yellow-500 rounded-full border-2 border-white flex items-center justify-center"
                     title="Email não verificado">
                    <svg class="{{ $statusIconClass }} text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                </div>
            @endif
        </div>
    @endif
</div>
