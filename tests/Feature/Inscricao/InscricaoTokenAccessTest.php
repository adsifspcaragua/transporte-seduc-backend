<?php

namespace Tests\Feature\Inscricao;

use App\Models\Inscricao;
use App\Models\InscricaoInstituicoes;
use App\Models\Instituicao;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * TESTE FUNCIONAL (caixa-preta) do controle de acesso da lista de espera.
 *
 * O estudante nao faz login: a inscricao criada devolve um token que funciona
 * como credencial dela. Sem o token, o ID sequencial nao pode dar acesso aos
 * dados pessoais nem permitir alteracoes.
 */
class InscricaoTokenAccessTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, mixed>
     */
    private function payloadValido(): array
    {
        return [
            'name' => 'Joao da Silva',
            'cpf' => '12345678901',
            'email' => 'joao@example.com',
        ];
    }

    /** Cria a inscricao pela rota publica e devolve [id, token]. */
    private function criarInscricao(): array
    {
        $dados = $this->postJson('/api/inscricoes', $this->payloadValido())
            ->assertCreated()
            ->json();

        return [$dados['id'], $dados['token']];
    }

    public function test_criacao_devolve_o_token_da_inscricao(): void
    {
        [$id, $token] = $this->criarInscricao();

        $this->assertNotEmpty($token);
        $this->assertSame($token, Inscricao::find($id)->access_token);
    }

    public function test_consulta_sem_token_e_recusada(): void
    {
        [$id] = $this->criarInscricao();

        $this->getJson("/api/inscricoes/{$id}")
            ->assertUnauthorized()
            ->assertJson(['message' => 'Token da inscrição inválido ou ausente.']);
    }

    public function test_consulta_com_token_de_outra_inscricao_e_recusada(): void
    {
        [$id] = $this->criarInscricao();
        $outra = Inscricao::factory()->create(['access_token' => 'token-de-outra-inscricao']);

        $this->getJson("/api/inscricoes/{$id}?token={$outra->access_token}")
            ->assertUnauthorized();
    }

    public function test_consulta_com_token_correto_devolve_a_inscricao(): void
    {
        [$id, $token] = $this->criarInscricao();

        $this->getJson("/api/inscricoes/{$id}?token={$token}")
            ->assertOk()
            ->assertJsonPath('data.cpf', '12345678901');
    }

    public function test_token_tambem_e_aceito_no_cabecalho(): void
    {
        [$id, $token] = $this->criarInscricao();

        $this->getJson("/api/inscricoes/{$id}", ['X-Inscricao-Token' => $token])
            ->assertOk();
    }

    public function test_atualizacao_sem_token_e_recusada(): void
    {
        [$id] = $this->criarInscricao();

        $this->patchJson("/api/inscricoes/{$id}", ['phone' => '12999990000'])
            ->assertUnauthorized();

        $this->assertNull(Inscricao::find($id)->phone);
    }

    public function test_atualizacao_com_token_e_aceita(): void
    {
        [$id, $token] = $this->criarInscricao();

        $this->patchJson("/api/inscricoes/{$id}", [
            'token' => $token,
            'phone' => '12999990000',
        ])->assertOk();

        $this->assertSame('12999990000', Inscricao::find($id)->phone);
    }

    public function test_dados_institucionais_exigem_o_token_da_inscricao(): void
    {
        [$id, $token] = $this->criarInscricao();
        $instituicao = Instituicao::factory()->create();

        $payload = [
            'course' => 'Engenharia de Software',
            'semester' => '3',
            'instituicao_id' => $instituicao->id,
        ];

        $this->postJson("/api/inscricoes/{$id}/instituicoes", $payload)
            ->assertUnauthorized();

        $this->postJson("/api/inscricoes/{$id}/instituicoes", [...$payload, 'token' => $token])
            ->assertSuccessful();

        $this->assertDatabaseHas('inscricao_instituicoes', ['inscricao_id' => $id]);
    }

    public function test_inscricao_inexistente_retorna_404(): void
    {
        $this->getJson('/api/inscricoes/999999?token=qualquer')
            ->assertNotFound();
    }

    public function test_equipe_autenticada_acessa_sem_token(): void
    {
        $this->seed(RolePermissionSeeder::class);
        [$id] = $this->criarInscricao();

        $gestor = User::factory()->create(['ativo' => true]);
        $gestor->roles()->sync(Role::where('title', 'gestor')->pluck('id'));
        Sanctum::actingAs($gestor);

        $this->getJson("/api/inscricoes/{$id}")->assertOk();
    }

    public function test_listagem_administrativa_nao_expoe_os_tokens(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $inscricao = Inscricao::factory()->create(['access_token' => 'token-secreto']);
        InscricaoInstituicoes::factory()->create(['inscricao_id' => $inscricao->id]);

        $gestor = User::factory()->create(['ativo' => true]);
        $gestor->roles()->sync(Role::where('title', 'gestor')->pluck('id'));
        Sanctum::actingAs($gestor);

        $resposta = $this->getJson('/api/inscricoes')->assertOk();

        $this->assertStringNotContainsString('token-secreto', $resposta->getContent());
    }
}
