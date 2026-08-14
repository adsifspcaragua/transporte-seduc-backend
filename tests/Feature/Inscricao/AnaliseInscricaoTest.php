<?php

namespace Tests\Feature\Inscricao;

use App\Models\Estudante;
use App\Models\Inscricao;
use App\Models\InscricaoInstituicoes;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * TESTE FUNCIONAL (caixa-preta) da decisao sobre a inscricao da lista de espera.
 *
 * Aprovar precisa gerar o estudante apto ao beneficio; recusar precisa registrar
 * o motivo. A analise nao depende mais de documentos.
 */
class AnaliseInscricaoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    private function autenticarComo(string $role): void
    {
        $user = User::factory()->create(['ativo' => true]);
        $user->roles()->sync(Role::where('title', $role)->pluck('id'));

        Sanctum::actingAs($user);
    }

    private function inscricaoCompleta(): Inscricao
    {
        $inscricao = Inscricao::factory()->create(['status' => 'Em analise']);
        InscricaoInstituicoes::factory()->create(['inscricao_id' => $inscricao->id]);

        return $inscricao;
    }

    public function test_aprovacao_cria_o_estudante_ativo(): void
    {
        $this->autenticarComo('gestor');
        $inscricao = $this->inscricaoCompleta();

        $this->putJson("/api/inscricoes/analise/{$inscricao->id}", ['decisao' => 'Aprovado'])
            ->assertOk();

        $estudante = Estudante::where('inscricao_id', $inscricao->id)->first();

        $this->assertSame('Aprovado', $inscricao->refresh()->status);
        $this->assertNotNull($estudante);
        $this->assertSame('Ativo', $estudante->status);
        $this->assertSame($inscricao->cpf, $estudante->cpf);
    }

    public function test_aprovacao_de_inscricao_sem_dados_institucionais_e_recusada(): void
    {
        $this->autenticarComo('gestor');
        $inscricao = Inscricao::factory()->create(['status' => 'Em analise']);

        $this->putJson("/api/inscricoes/analise/{$inscricao->id}", ['decisao' => 'Aprovado'])
            ->assertStatus(422);

        $this->assertSame('Em analise', $inscricao->refresh()->status);
        $this->assertDatabaseCount('estudantes', 0);
    }

    public function test_rejeicao_exige_motivo(): void
    {
        $this->autenticarComo('gestor');
        $inscricao = $this->inscricaoCompleta();

        $this->putJson("/api/inscricoes/analise/{$inscricao->id}", ['decisao' => 'Rejeitado'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('motivo');
    }

    public function test_rejeicao_registra_o_motivo_na_inscricao(): void
    {
        $this->autenticarComo('gestor');
        $inscricao = $this->inscricaoCompleta();

        $this->putJson("/api/inscricoes/analise/{$inscricao->id}", [
            'decisao' => 'Rejeitado',
            'motivo' => 'Estudante reside fora do município.',
        ])->assertOk();

        $inscricao->refresh();

        $this->assertSame('Rejeitado', $inscricao->status);
        $this->assertSame('Estudante reside fora do município.', $inscricao->observation);
        $this->assertDatabaseCount('estudantes', 0);
    }

    public function test_decisao_invalida_e_rejeitada(): void
    {
        $this->autenticarComo('gestor');
        $inscricao = $this->inscricaoCompleta();

        $this->putJson("/api/inscricoes/analise/{$inscricao->id}", ['decisao' => 'Talvez'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('decisao');
    }

    public function test_operador_nao_pode_analisar_inscricao(): void
    {
        $this->autenticarComo('operador');
        $inscricao = $this->inscricaoCompleta();

        $this->putJson("/api/inscricoes/analise/{$inscricao->id}", ['decisao' => 'Aprovado'])
            ->assertForbidden();
    }
}
