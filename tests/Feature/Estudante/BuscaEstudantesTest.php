<?php

namespace Tests\Feature\Estudante;

use App\Models\Estudante;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BuscaEstudantesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create(['ativo' => true]);
        $user->roles()->sync(Role::where('title', 'gestor')->pluck('id'));
        Sanctum::actingAs($user);
    }

    public function test_busca_por_nome_filtra_antes_de_paginar(): void
    {
        Estudante::factory()->create(['name' => 'Bruna Souza']);
        Estudante::factory()->count(16)->create(['name' => 'Ana Silva']);
        $this->getJson('/api/estudantes?search=Ana&per_page=15&page=2')
            ->assertOk()->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Ana Silva')->assertJsonPath('meta.total', 16);
    }

    public function test_busca_vazia_de_resultados_preserva_paginacao(): void
    {
        $this->getJson('/api/estudantes?search=Inexistente')
            ->assertOk()->assertJsonCount(0, 'data')->assertJsonPath('meta.total', 0);
    }

    public function test_busca_valida_o_tamanho_e_nao_aceita_arrays(): void
    {
        $this->getJson('/api/estudantes?search='.str_repeat('a', 101))->assertUnprocessable();
        $this->getJson('/api/estudantes?search[]=Ana')->assertUnprocessable();
    }

    public function test_motorista_nao_pode_pesquisar_cadastro(): void
    {
        $user = User::factory()->create(['ativo' => true]);
        $user->roles()->sync(Role::where('title', 'motorista')->pluck('id'));
        Sanctum::actingAs($user);
        $this->getJson('/api/estudantes?search=Ana')->assertForbidden();
    }
}
