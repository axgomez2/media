<x-admin-layout title="Adicionar Novo Cliente">
    <div class="container px-6 mx-auto grid">
        <h2 class="my-6 text-2xl font-semibold text-gray-700">
            Adicionar Novo Cliente
        </h2>

        <div class="px-4 py-3 mb-8 bg-white rounded-lg shadow-md">
            <form action="{{ route('admin.clients.store') }}" method="POST">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Dados Pessoais -->
                    <div class="col-span-1">
                        <h3 class="text-lg font-medium text-gray-800 mb-4">Dados Pessoais</h3>
                        <label class="block text-sm">
                            <span class="text-gray-700">Nome</span>
                            <input type="text" name="name" class="block w-full mt-1 text-sm form-input" placeholder="Nome completo do cliente" value="{{ old('name') }}" required>
                            @error('name')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
                        </label>

                        <label class="block mt-4 text-sm">
                            <span class="text-gray-700">Email</span>
                            <input type="email" name="email" class="block w-full mt-1 text-sm form-input" placeholder="email@exemplo.com" value="{{ old('email') }}" required>
                            @error('email')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
                        </label>

                        <label class="block mt-4 text-sm">
                            <span class="text-gray-700">CPF</span>
                            <input type="text" name="cpf" class="block w-full mt-1 text-sm form-input" placeholder="000.000.000-00" value="{{ old('cpf') }}">
                            @error('cpf')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
                        </label>

                        <label class="block mt-4 text-sm">
                            <span class="text-gray-700">Telefone</span>
                            <input type="text" name="phone" class="block w-full mt-1 text-sm form-input" placeholder="(00) 00000-0000" value="{{ old('phone') }}">
                            @error('phone')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
                        </label>
                    </div>

                    <!-- Endereço -->
                    <div class="col-span-1">
                        <h3 class="text-lg font-medium text-gray-800 mb-4">Endereço Principal</h3>
                        <label class="block text-sm">
                            <span class="text-gray-700">CEP</span>
                            <input type="text" id="zip_code" name="zip_code" class="block w-full mt-1 text-sm form-input" placeholder="00000-000" value="{{ old('zip_code') }}" required>
                            @error('zip_code')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
                        </label>

                        <label class="block mt-4 text-sm">
                            <span class="text-gray-700">Rua</span>
                            <input type="text" id="street" name="street" class="block w-full mt-1 text-sm form-input" placeholder="Rua, Avenida, etc." value="{{ old('street') }}" required>
                            @error('street')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
                        </label>

                        <div class="grid grid-cols-2 gap-4 mt-4">
                            <label class="block text-sm">
                                <span class="text-gray-700">Número</span>
                                <input type="text" id="number" name="number" class="block w-full mt-1 text-sm form-input" value="{{ old('number') }}" required>
                                @error('number')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
                            </label>
                            <label class="block text-sm">
                                <span class="text-gray-700">Complemento</span>
                                <input type="text" id="complement" name="complement" class="block w-full mt-1 text-sm form-input" value="{{ old('complement') }}">
                            </label>
                        </div>

                        <label class="block mt-4 text-sm">
                            <span class="text-gray-700">Bairro</span>
                            <input type="text" id="neighborhood" name="neighborhood" class="block w-full mt-1 text-sm form-input" value="{{ old('neighborhood') }}" required>
                            @error('neighborhood')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
                        </label>

                        <div class="grid grid-cols-2 gap-4 mt-4">
                            <label class="block text-sm">
                                <span class="text-gray-700">Cidade</span>
                                <input type="text" id="city" name="city" class="block w-full mt-1 text-sm form-input" value="{{ old('city') }}" required>
                                @error('city')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
                            </label>
                            <label class="block text-sm">
                                <span class="text-gray-700">Estado</span>
                                <input type="text" id="state" name="state" class="block w-full mt-1 text-sm form-input" maxlength="2" value="{{ old('state') }}" required>
                                @error('state')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
                            </label>
                        </div>
                    </div>
                </div>

                <div class="mt-6">
                    <button type="submit" class="px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-lg active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple">
                        Criar Cliente
                    </button>
                    <a href="{{ route('admin.clients.index') }}" class="px-4 py-2 text-sm font-medium leading-5 text-gray-700 transition-colors duration-150 bg-gray-200 border border-transparent rounded-lg active:bg-gray-300 hover:bg-gray-300 focus:outline-none focus:shadow-outline-gray">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        document.getElementById('zip_code').addEventListener('blur', function() {
            const zipCode = this.value.replace(/\D/g, '');
            if (zipCode.length === 8) {
                fetch(`https://viacep.com.br/ws/${zipCode}/json/`)
                    .then(response => response.json())
                    .then(data => {
                        if (!data.erro) {
                            document.getElementById('street').value = data.logradouro;
                            document.getElementById('neighborhood').value = data.bairro;
                            document.getElementById('city').value = data.localidade;
                            document.getElementById('state').value = data.uf;
                            document.getElementById('number').focus(); // Foca no campo de número
                        }
                    })
                    .catch(error => console.error('Erro ao buscar CEP:', error));
            }
        });
    </script>
    @endpush
</x-admin-layout>
