<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin UNIBUS',
                'password' => Hash::make('12345678'),
                'cpf' => '12345678901',
                'matricula' => 1001,
                'data_nascimento' => '2000-01-01',
                'ativo' => true,
            ],
        );

        $operador = User::updateOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'Operador UNIBUS',
                'password' => Hash::make('12345678'),
                'cpf' => '12345678902',
                'matricula' => 1002,
                'data_nascimento' => '2001-02-02',
                'ativo' => true,
            ],
        );

        $admin->roles()->sync(Role::where('title', 'admin')->pluck('id'));
        $operador->roles()->sync(Role::where('title', 'operador')->pluck('id'));
    }
}
