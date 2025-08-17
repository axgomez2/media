@extends('layouts.auth')

@section('content')
<div class="max-w-md mx-auto mt-20 bg-white p-8 rounded shadow">
    <h1 class="text-xl font-bold mb-4 text-center">Login</h1>

    @if(session('error'))
        <div class="text-red-500 mb-4">
            {{ session('error') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="mb-4">
            <label for="email" class="block font-medium">Email</label>
            <input type="email" name="email" id="email" required autofocus
                   class="w-full border px-3 py-2 rounded">
            @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="mb-4">
            <label for="password" class="block font-medium">Senha</label>
            <input type="password" name="password" id="password" required
                   class="w-full border px-3 py-2 rounded">
            @error('password') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="mb-4">
            <label class="inline-flex items-center">
                <input type="checkbox" name="remember" class="mr-2">
                Lembrar-me
            </label>
        </div>

        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded w-full">
            Entrar
        </button>
    </form>
</div>
@endsection
