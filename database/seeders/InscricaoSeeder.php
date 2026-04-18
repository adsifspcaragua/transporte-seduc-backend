<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Inscricao;

class InscricaoSeeder extends Seeder
{
    public function run(): void
    {
        Inscricao::create([
            "name" => "João da Silva",
            "cpf" => "11111111111",
            "rg" => "12345678",
            "birth_date" => "2005-03-15",
            "phone" => "11999999999",
            "email" => "joao@example.com",
            "cep" => "01001000",
            "address" => "Rua A",
            "neighborhood" => "Centro",
            "city" => "São Paulo",
            "number" => "123",
            "father_name" => "José da Silva",
            "mother_name" => "Maria da Silva",
            "observation" => "Inscrição teste",
            "status" => "pendente",
            "accepted_terms" => true,
            "accepted_terms_2" => true
        ]);

        Inscricao::create([
            "name" => "Maria Oliveira",
            "cpf" => "22222222222",
            "rg" => "87654321",
            "birth_date" => "2006-05-20",
            "phone" => "11888888888",
            "email" => "maria@example.com",
            "cep" => "02002000",
            "address" => "Rua B",
            "neighborhood" => "Zona Norte",
            "city" => "São Paulo",
            "number" => "456",
            "father_name" => "Carlos Oliveira",
            "mother_name" => "Ana Oliveira",
            "observation" => null,
            "status" => "pendente",
            "accepted_terms" => true,
            "accepted_terms_2" => true
        ]);
    }
}