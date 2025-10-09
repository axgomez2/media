<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClientUser;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index()
    {
        $clients = ClientUser::latest()->paginate(20);
        return view('admin.clients.index', compact('clients'));
    }

    public function create()
    {
        return view('admin.clients.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            // Client data
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:client_users'],
            'cpf' => ['nullable', 'string', 'max:14'], // Pode adicionar uma regra de validação de CPF mais robusta aqui
            'phone' => ['nullable', 'string', 'max:20'],

            // Address data
            'zip_code' => ['required', 'string', 'max:9'],
            'street' => ['required', 'string', 'max:255'],
            'number' => ['required', 'string', 'max:20'],
            'complement' => ['nullable', 'string', 'max:100'],
            'neighborhood' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:2'],
        ]);

        $password = \Illuminate\Support\Str::random(10);

        $client = ClientUser::create([
            'name' => $request->name,
            'email' => $request->email,
            'cpf' => $request->cpf,
            'phone' => $request->phone,
            'password' => \Illuminate\Support\Facades\Hash::make($password),
            'email_verified_at' => now(),
        ]);

        $client->addresses()->create([
            'zip_code' => $request->zip_code,
            'street' => $request->street,
            'number' => $request->number,
            'complement' => $request->complement,
            'neighborhood' => $request->neighborhood,
            'city' => $request->city,
            'state' => $request->state,
            'is_default' => true, // O primeiro endereço é o padrão
        ]);

        // Enviar e-mail para o novo cliente com a senha
        \Illuminate\Support\Facades\Mail::to($client->email)->send(new \App\Mail\NewClientAccount($client, $password));

        return redirect()->route('admin.clients.index')
            ->with('success', 'Cliente e endereço criados com sucesso!');
    }

    public function edit(ClientUser $client)
    {
        // Lógica para mostrar o formulário de edição será adicionada aqui
    }

    public function update(Request $request, ClientUser $client)
    {
        // Lógica para atualizar o cliente será adicionada aqui
    }

    public function destroy(ClientUser $client)
    {
        // Lógica para deletar o cliente será adicionada aqui
    }
}
