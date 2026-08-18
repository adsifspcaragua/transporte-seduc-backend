<?php

namespace Tests\Feature\Dashboard;

use App\Models\Estudante;
use App\Models\Inscricao;
use App\Models\Linha;
use App\Models\PeriodoReecadastro;
use App\Models\Role;
use App\Models\SolicitacaoReecadastro;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * TESTE FUNCIONAL dos numeros da tela inicial.
 *
 * A tela responde "o que precisa da minha atencao hoje", entao o que importa e a
 * separacao entre pendencia e panorama: um numero errado aqui faz a responsavel
 * procurar trabalho que nao existe, ou ignorar o que existe.
 */
class DashboardTest extends TestCase
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

    private function resumo(): array
    {
        return $this->getJson('/api/dashboard')->assertOk()->json('data');
    }

    public function test_conta_estudantes_por_situacao(): void
    {
        Estudante::factory()->count(2)->create(['status' => 'Ativo']);
        Estudante::factory()->create(['status' => 'Inativo']);
        Estudante::factory()->create(['status' => 'Em espera']);

        $estudantes = $this->resumo()['estudantes'];

        $this->assertSame(2, $estudantes['ativos']);
        $this->assertSame(1, $estudantes['inativos']);
        $this->assertSame(1, $estudantes['em_espera']);
        $this->assertSame(4, $estudantes['total']);
    }

    public function test_conta_apenas_ativos_sem_linha(): void
    {
        $linha = Linha::factory()->create(['max_capacity' => 10]);

        Estudante::factory()->create(['status' => 'Ativo', 'linha_id' => null]);
        Estudante::factory()->create(['status' => 'Ativo', 'linha_id' => $linha->id]);
        // Inativo sem linha nao e pendencia: ele nao anda de onibus.
        Estudante::factory()->create(['status' => 'Inativo', 'linha_id' => null]);

        $this->assertSame(1, $this->resumo()['estudantes']['sem_linha']);
    }

    public function test_conta_inscricoes_por_status(): void
    {
        Inscricao::factory()->create(['status' => 'Em analise']);
        Inscricao::factory()->count(2)->create(['status' => 'Incompleto']);
        Inscricao::factory()->create(['status' => 'Aprovado']);

        $inscricoes = $this->resumo()['inscricoes'];

        $this->assertSame(1, $inscricoes['em_analise']);
        $this->assertSame(2, $inscricoes['incompletas']);
        $this->assertSame(1, $inscricoes['aprovadas']);
    }

    public function test_ocupacao_das_linhas_conta_so_ativos(): void
    {
        $linha = Linha::factory()->create(['max_capacity' => 10]);
        Estudante::factory()->count(2)->create(['linha_id' => $linha->id, 'status' => 'Ativo']);
        Estudante::factory()->create(['linha_id' => $linha->id, 'status' => 'Inativo']);

        $linhas = $this->resumo()['linhas'];

        $this->assertSame(10, $linhas['capacidade_total']);
        $this->assertSame(2, $linhas['ocupacao_total']);
        $this->assertSame(2, $linhas['lista'][0]['ocupacao']);
        $this->assertSame(8, $linhas['lista'][0]['vagas_restantes']);
    }

    public function test_sem_periodo_aberto_o_recadastro_vem_zerado(): void
    {
        Estudante::factory()->count(3)->create(['status' => 'Ativo']);

        $recadastro = $this->resumo()['recadastro'];

        // Sem periodo aberto ninguem esta em falta: cobrar recadastro fora de
        // prazo seria inventar pendencia.
        $this->assertNull($recadastro['periodo']);
        $this->assertSame(0, $recadastro['ausentes']);
    }

    public function test_com_periodo_aberto_conta_quem_falta(): void
    {
        $periodo = PeriodoReecadastro::factory()->create(['status' => 'Aberto']);

        $emDia = Estudante::factory()->create(['status' => 'Ativo']);
        SolicitacaoReecadastro::create([
            'estudante_id' => $emDia->id,
            'periodo_id' => $periodo->id,
            'status' => 'Aprovado',
        ]);

        $emAnalise = Estudante::factory()->create(['status' => 'Ativo']);
        SolicitacaoReecadastro::create([
            'estudante_id' => $emAnalise->id,
            'periodo_id' => $periodo->id,
            'status' => 'Em analise',
        ]);

        Estudante::factory()->count(2)->create(['status' => 'Ativo']);

        $recadastro = $this->resumo()['recadastro'];

        $this->assertSame($periodo->referencia, $recadastro['periodo']['referencia']);
        $this->assertSame(1, $recadastro['em_analise']);
        // Aprovado e "Em analise" nao contam como ausentes.
        $this->assertSame(2, $recadastro['ausentes']);
    }

    public function test_exige_autenticacao(): void
    {
        app('auth')->forgetGuards();

        $this->getJson('/api/dashboard')->assertUnauthorized();
    }
}
