@if ($errors->any())
    <x-admin.alert type="error" title="Erro de Validação" class="mb-4">
        <ul class="mt-2 list-disc list-inside space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </x-admin.alert>
@endif
