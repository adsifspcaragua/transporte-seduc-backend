<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
      
        User::create([
            'name' => 'Admin Teste',
            'email' => 'admin@example.com',
            'password' => Hash::make('12345678'),
            'cpf' => '12345678901',
            'matricula' => 1001,
            'data_nascimento' => '2000-01-01',
        ]);

        User::create([
            'name' => 'User Teste',
            'email' => 'user@example.com',
            'password' => Hash::make('12345678'),
            'cpf' => '12345678902',
            'matricula' => 1002,
            'data_nascimento' => '2001-02-02',
        ]);
    }
    
}
