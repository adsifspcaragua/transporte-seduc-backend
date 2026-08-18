<?php

namespace Tests\Feature\Reecadastro;

use App\Models\Estudante;
use App\Models\PeriodoReecadastro;
use App\Models\Role;
use App\Models\SolicitacaoReecadastro;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * TESTE FUNCIONAL da lista de quem nao recadastrou.
 *
 * A solicitacao so nasce quando o estudante acessa pelo CPF, entao quem nunca
 * entrou nao aparece na tela de solicitacoes. Sem esta lista a responsavel nao
 * tem como saber quem faltou, e o recadastro fica sem consequencia: quem ignora
 * segue ativo com direito ao transporte.
 */
class AusentesReecadastroTest extends TestCase
{
    use RefreshDatabase;

    private PeriodoReecadastro $periodo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);

        $this->periodo = PeriodoReecadastro::factory()->create(['status' => 'Aberto']);

        $user = User::factory()->create(['ativo' => true]);
        $user->roles()->sync(Role::where('title', 'admin')->pluck('id'));
        Sanctum::actingAs($user);
    }

    private function estudanteCom(?string $statusSolicitacao, string $statusEstudante = 'Ativo'): Estudante
    {
        $estudante = Estudante::factory()->create(['status' => $statusEstudante]);

        if ($statusSolicitacao !== null) {
            SolicitacaoReecadastro::create([
                'estudante_id' => $estudante->id,
                'periodo_id' => $this->periodo->id,
                'status' => $statusSolicitacao,
            ]);
        }

        return $estudante;
    }

    public function test_lista_traz_quem_nao_concluiu_o_recadastro(): void
    {
        $naoIniciou = $this->estudanteCom(null);
        $naoEnviou = $this->estudanteCom('Pendente');
        $devolvido = $this->estudanteCom('Pendencia');
        $aprovado = $this->estudanteCom('Aprovado');
        $emAnalise = $this->estudanteCom('Em analise');

        $ids = collect(
            $this->getJson("/api/reecadastro/periodos/{$this->periodo->id}/ausentes")
                ->assertOk()
                ->json('data')
        )->pluck('id');

        $this->assertTrue($ids->contains($naoIniciou->id));
        $this->assertTrue($ids->contains($naoEnviou->id));
        $this->assertTrue($ids->contains($devolvido->id));

        // Aprovado esta em dia; "Em analise" esta com a responsavel, nao com o
        // estudante, entao nenhum dos dois conta como ausente.
        $this->assertFalse($ids->contains($aprovado->id));
        $this->assertFalse($ids->contains($emAnalise->id));
    }

    public function test_estudante_ja_inativo_nao_aparece(): void
    {
        $inativo = $this->estudanteCom(null, 'Inativo');

        $ids = collect(
            $this->getJson("/api/reecadastro/periodos/{$this->periodo->id}/ausentes")
                ->json('data')
        )->pluck('id');

        $this->assertFalse($ids->contains($inativo->id));
    }

    public function test_situacao_explica_onde_o_estudante_parou(): void
    {
        $this->estudanteCom('Pendencia');

        $situacao = $this->getJson("/api/reecadastro/periodos/{$this->periodo->id}/ausentes")
            ->json('data.0.situacao');

        $this->assertSame('Devolvido e não corrigiu', $situacao);
    }

    public function test_inativa_apenas_os_estudantes_selecionados(): void
    {
        $alvo = $this->estudanteCom(null);
        $poupado = $this->estudanteCom(null);

        $this->postJson("/api/reecadastro/periodos/{$this->periodo->id}/inativar-ausentes", [
            'estudantes' => [$alvo->id],
        ])->assertOk();

        $this->assertSame('Inativo', $alvo->refresh()->status);
        $this->assertSame('Ativo', $poupado->refresh()->status);
    }

    public function test_quem_recadastrou_nao_e_inativado_nem_em_lista_mista(): void
    {
        $ausente = $this->estudanteCom(null);
        $aprovado = $this->estudanteCom('Aprovado');

        $resposta = $this->postJson("/api/reecadastro/periodos/{$this->periodo->id}/inativar-ausentes", [
            'estudantes' => [$ausente->id, $aprovado->id],
        ])->assertOk();

        $this->assertSame('Inativo', $ausente->refresh()->status);
        $this->assertSame('Ativo', $aprovado->refresh()->status);
        $this->assertSame([$aprovado->id], $resposta->json('ignorados'));
    }

    public function test_inativar_so_quem_esta_em_dia_e_recusado(): void
    {
        $aprovado = $this->estudanteCom('Aprovado');

        $this->postJson("/api/reecadastro/periodos/{$this->periodo->id}/inativar-ausentes", [
            'estudantes' => [$aprovado->id],
        ])->assertStatus(422);

        $this->assertSame('Ativo', $aprovado->refresh()->status);
    }

    public function test_lista_vazia_e_recusada(): void
    {
        $this->postJson("/api/reecadastro/periodos/{$this->periodo->id}/inativar-ausentes", [
            'estudantes' => [],
        ])->assertStatus(422)->assertJsonValidationErrors('estudantes');
    }

    public function test_operador_sem_permissao_nao_inativa(): void
    {
        $ausente = $this->estudanteCom(null);

        $operador = User::factory()->create(['ativo' => true]);
        $operador->roles()->sync(Role::where('title', 'operador')->pluck('id'));
        Sanctum::actingAs($operador);

        $this->postJson("/api/reecadastro/periodos/{$this->periodo->id}/inativar-ausentes", [
            'estudantes' => [$ausente->id],
        ])->assertForbidden();

        $this->assertSame('Ativo', $ausente->refresh()->status);
    }
}
