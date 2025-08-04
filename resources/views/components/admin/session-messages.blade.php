@if(session('success'))
    <x-admin.alert type="success" class="mb-4">
        {{ session('success') }}
    </x-admin.alert>
@endif

@if(session('error'))
    <x-admin.alert type="error" class="mb-4">
        {{ session('error') }}
    </x-admin.alert>
@endif

@if(session('warning'))
    <x-admin.alert type="warning" class="mb-4">
        {{ session('warning') }}
    </x-admin.alert>
@endif

@if(session('info'))
    <x-admin.alert type="info" class="mb-4">
        {{ session('info') }}
    </x-admin.alert>
@endif
