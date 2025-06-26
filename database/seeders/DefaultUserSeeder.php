<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class DefaultUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Insere um usuário padrão na tabela users (gestão interna)
        DB::table('users')->insert([
            'id'         => (string) Str::uuid(),
            'name'       => 'padrao',
            'email'      => 'padrao@uol.com.br',
            'password'   => Hash::make('Mudar123'),
            'role'       => 66,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
