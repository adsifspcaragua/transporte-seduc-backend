<?php

namespace Tests\Feature\Seguranca;

use App\Models\Chamada;
use App\Models\Estudante;
use App\Models\Frequencia;
use App\Models\HorarioViagem;
use App\Models\Linha;
use App\Models\Presenca;
use App\Models\RegistroAuditoria;
use App\Models\User;
use App\Models\Viagem;
use App\Services\Viagem\CargaInicialPresenca;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Tests\Support\CenarioPresenca;
use Tests\TestCase;

/**
 * TESTE FUNCIONAL dos pontos encontrados na analise do projeto: conta inativa,
 * dashboard, exclusao com historico, fuso da chamada, conta unica do estudante
 * e as duas representacoes do horario da linha.
 */
class CorrecoesDeAcessoTest extends TestCase
{
    use CenarioPresenca, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    private function presencaNa(Linha $linha, Estudante $estudante): Presenca
    {
        $viagem = Viagem::create([
            'linha_id' => $linha->id,
            'sentido' => HorarioViagem::IDA,
            'data' => '2026-10-19',
            'horario_saida' => '17:30:00',
            'janela_inicio' => '2026-10-19 20:15:00',
            'janela_fim' => '2026-10-19 20:45:00',
            'status' => Viagem::PROGRAMADA,
        ]);

        return Presenca::create([
            'estudante_id' => $estudante->id,
            'viagem_id' => $viagem->id,
            'metodo' => Presenca::MANUAL,
            'registrada_em' => now(),
        ]);
    }

    public function test_rotas_de_login_do_fortify_nao_existem_mais(): void
    {
        $inativo = User::factory()->create(['ativo' => false, 'password' => 'segredo123']);

        $this->postJson('/login', ['email' => $inativo->email, 'password' => 'segredo123'])->assertNotFound();
        $this->postJson('/register', [])->assertNotFound();
        $this->assertGuest('web');
    }

    public function test_sem_autenticacao_e_sem_aceitar_json_ainda_responde_401(): void
    {
        $this->get('/api/me')->assertUnauthorized();
    }

    public function test_conta_inativa_nao_usa_as_rotas_autenticadas_mas_consegue_sair(): void
    {
        Sanctum::actingAs($this->usuario('operador'));
        $this->getJson('/api/me')->assertOk();

        $inativo = $this->usuario('operador');
        $inativo->update(['ativo' => false]);
        Sanctum::actingAs($inativo);

        $this->getJson('/api/me')->assertForbidden()->assertJsonPath('message', 'Usuário inativo.');
        $this->getJson('/api/dashboard')->assertForbidden();
        $this->postJson('/api/auth/token/revoke')->assertOk();
    }

    public function test_dashboard_e_da_equipe(): void
    {
        foreach (['admin', 'gestor', 'operador', 'motorista'] as $perfil) {
            Sanctum::actingAs($this->usuario($perfil));
            $this->getJson('/api/dashboard')->assertOk();
        }

        Sanctum::actingAs($this->usuario('estudante'));
        $this->getJson('/api/dashboard')->assertForbidden();
    }

    public function test_linha_com_presenca_nao_e_excluida(): void
    {
        $linha = Linha::factory()->create();
        $this->presencaNa($linha, Estudante::factory()->create(['linha_id' => null]));

        Sanctum::actingAs($this->usuario('gestor'));
        $this->deleteJson("/api/linha/{$linha->id}")->assertStatus(409);

        $this->assertSame(1, Presenca::count());
        $this->assertNotNull($linha->fresh());
    }

    public function test_linha_so_com_viagens_e_excluida_e_fica_na_auditoria(): void
    {
        $linha = Linha::factory()->create();
        $presenca = $this->presencaNa($linha, Estudante::factory()->create(['linha_id' => null]));
        $presenca->delete();

        Sanctum::actingAs($this->usuario('gestor'));
        $this->deleteJson("/api/linha/{$linha->id}")->assertOk();

        $registro = RegistroAuditoria::where('acao', 'linha.excluir')->sole();
        $this->assertSame(1, $registro->dados['viagens_removidas']);
    }

    public function test_estudante_com_presenca_nao_e_excluido(): void
    {
        $linha = Linha::factory()->create();
        $estudante = Estudante::factory()->create(['linha_id' => $linha->id]);
        $this->presencaNa($linha, $estudante);

        Sanctum::actingAs($this->usuario('gestor'));
        $this->deleteJson("/api/estudantes/{$estudante->id}")->assertStatus(409);

        $this->assertSame(1, Presenca::count());
    }

    public function test_estudante_sem_presenca_e_excluido_e_fica_na_auditoria(): void
    {
        $estudante = Estudante::factory()->create();

        Sanctum::actingAs($this->usuario('gestor'));
        $this->deleteJson("/api/estudantes/{$estudante->id}")->assertOk();

        $this->assertTrue(RegistroAuditoria::where('acao', 'estudante.excluir')->exists());
    }

    public function test_estudante_com_frequencia_marcada_nao_e_excluido(): void
    {
        $linha = Linha::factory()->create();
        $estudante = Estudante::factory()->create(['linha_id' => $linha->id]);
        $chamada = Chamada::factory()->create(['linha_id' => $linha->id]);
        Frequencia::factory()->create(['chamada_id' => $chamada->id, 'estudante_id' => $estudante->id, 'situacao' => Frequencia::FALTA]);

        Sanctum::actingAs($this->usuario('gestor'));
        $this->deleteJson("/api/estudantes/{$estudante->id}")->assertStatus(409);

        $this->assertSame(1, Frequencia::count());
        $this->assertNotNull($estudante->fresh());
    }

    public function test_frequencia_ainda_pendente_nao_impede_a_exclusao(): void
    {
        $linha = Linha::factory()->create();
        $estudante = Estudante::factory()->create(['linha_id' => $linha->id]);
        $chamada = Chamada::factory()->create(['linha_id' => $linha->id]);
        Frequencia::factory()->create(['chamada_id' => $chamada->id, 'estudante_id' => $estudante->id, 'situacao' => Frequencia::PENDENTE]);

        Sanctum::actingAs($this->usuario('gestor'));
        $this->deleteJson("/api/estudantes/{$estudante->id}")->assertOk();

        $this->assertSame(0, Frequencia::count());
        $this->assertNull($estudante->fresh());
    }

    public function test_banco_tambem_impede_apagar_frequencia_em_cascata(): void
    {
        $linha = Linha::factory()->create();
        $estudante = Estudante::factory()->create(['linha_id' => $linha->id]);
        $chamada = Chamada::factory()->create(['linha_id' => $linha->id]);
        Frequencia::factory()->create(['chamada_id' => $chamada->id, 'estudante_id' => $estudante->id, 'situacao' => Frequencia::FALTA]);

        $this->expectException(QueryException::class);

        $estudante->delete();
    }

    public function test_excluir_a_chamada_ainda_leva_as_marcacoes_dela(): void
    {
        $linha = Linha::factory()->create();
        $estudante = Estudante::factory()->create(['linha_id' => $linha->id]);
        $chamada = Chamada::factory()->create(['linha_id' => $linha->id]);
        Frequencia::factory()->create(['chamada_id' => $chamada->id, 'estudante_id' => $estudante->id, 'situacao' => Frequencia::FALTA]);

        Sanctum::actingAs($this->usuario('gestor'));
        $this->deleteJson("/api/frequencias/chamadas/{$chamada->id}")->assertOk();

        $this->assertSame(0, Frequencia::count());
    }

    /**
     * Depois de config:cache o .env nao e lido: env() fora de config/ devolve
     * nulo. Por isso o link vem do config, e este teste falharia com env().
     */
    public function test_link_de_senha_aponta_para_o_frontend_configurado(): void
    {
        Notification::fake();
        config(['app.frontend_url' => 'https://sigtu.example.gov.br/', 'app.url' => 'https://api.example.gov.br']);
        $conta = User::factory()->create(['email' => 'ana@example.com']);

        $this->postJson('/api/password/email', ['email' => 'ana@example.com'])->assertOk();

        Notification::assertSentTo($conta, ResetPassword::class, function (ResetPassword $aviso) use ($conta) {
            $url = $aviso->toMail($conta)->actionUrl;

            return str_starts_with($url, 'https://sigtu.example.gov.br/reset-password?token=')
                && str_contains($url, 'email=ana%40example.com');
        });
    }

    public function test_banco_tambem_impede_apagar_presenca_em_cascata(): void
    {
        $linha = Linha::factory()->create();
        $estudante = Estudante::factory()->create(['linha_id' => $linha->id]);
        $this->presencaNa($linha, $estudante);

        $this->expectException(QueryException::class);

        $estudante->delete();
    }

    /**
     * A aplicacao roda em UTC: as 22h de Caraguatatuba ja e o dia seguinte nela.
     */
    public function test_chamada_a_noite_usa_o_dia_de_caraguatatuba(): void
    {
        $linha = Linha::factory()->create();
        Estudante::factory()->create(['linha_id' => $linha->id, 'days_of_week' => [1, 2, 3, 4, 5]]);
        Sanctum::actingAs($this->usuario('gestor'));

        $this->travelTo($this->local('2026-10-19 22:00:00'));

        $this->postJson('/api/frequencias/chamadas', ['linha_id' => $linha->id, 'data' => '2026-10-20'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('data');

        $id = $this->postJson('/api/frequencias/chamadas', ['linha_id' => $linha->id, 'data' => '2026-10-19'])
            ->assertCreated()
            ->json('data.id');

        $this->getJson('/api/frequencias/linhas')
            ->assertOk()
            ->assertJsonPath('data.0.chamada_hoje.id', $id);
    }

    public function test_cria_a_conta_do_estudante_e_envia_o_convite(): void
    {
        Notification::fake();
        $estudante = Estudante::factory()->create(['email' => 'ana@example.com', 'cpf' => '52998224725']);
        $gestor = $this->usuario('gestor');
        Sanctum::actingAs($gestor);

        $resposta = $this->postJson("/api/estudantes/{$estudante->id}/conta")
            ->assertCreated()
            ->assertJsonPath('data.convite_enviado', true)
            ->assertJsonMissingPath('data.password');

        $conta = User::find($resposta->json('data.user_id'));
        $this->assertSame(['estudante'], $conta->roleTitles());
        $this->assertSame($conta->id, $estudante->refresh()->user_id);
        Notification::assertSentTo($conta, ResetPassword::class);
        $this->assertTrue(RegistroAuditoria::where('acao', 'estudante.conta.criar')->where('user_id', $gestor->id)->exists());

        $this->postJson("/api/estudantes/{$estudante->id}/conta")->assertStatus(409);
    }

    public function test_conta_nao_e_criada_em_cima_de_outra_pessoa_nem_sem_beneficio(): void
    {
        Sanctum::actingAs($this->usuario('gestor'));

        $mesmoEmail = Estudante::factory()->create(['email' => 'dono@example.com']);
        User::factory()->create(['email' => 'dono@example.com']);
        $this->postJson("/api/estudantes/{$mesmoEmail->id}/conta")->assertStatus(409);
        $this->assertNull($mesmoEmail->refresh()->user_id);

        $mesmoCpf = Estudante::factory()->create(['cpf' => '52998224725']);
        User::factory()->create(['cpf' => '529.982.247-25']);
        $this->postJson("/api/estudantes/{$mesmoCpf->id}/conta")->assertStatus(409);

        $inativo = Estudante::factory()->inativo()->create();
        $this->postJson("/api/estudantes/{$inativo->id}/conta")->assertStatus(422);

        Sanctum::actingAs($this->usuario('motorista'));
        $this->postJson("/api/estudantes/{$inativo->id}/conta")->assertForbidden();
    }

    public function test_uma_conta_nao_fica_com_dois_estudantes(): void
    {
        $conta = User::factory()->create();
        Estudante::factory()->create(['user_id' => $conta->id]);

        $this->expectException(QueryException::class);

        Estudante::factory()->create(['user_id' => $conta->id]);
    }

    public function test_cadastro_recusa_conta_ja_usada_por_outro_estudante(): void
    {
        $conta = User::factory()->create();
        Estudante::factory()->create(['user_id' => $conta->id]);
        Sanctum::actingAs($this->usuario('gestor'));

        $this->postJson('/api/estudantes', ['user_id' => $conta->id])->assertJsonValidationErrors('user_id');
    }

    public function test_apagar_a_conta_solta_o_estudante(): void
    {
        $conta = User::factory()->create();
        $estudante = Estudante::factory()->create(['user_id' => $conta->id]);

        $conta->delete();

        $this->assertNull($estudante->refresh()->user_id);
    }

    public function test_criar_linha_ja_cria_os_horarios(): void
    {
        Sanctum::actingAs($this->usuario('gestor'));

        $id = $this->postJson('/api/linha', [
            'name' => 'Linha Nova',
            'max_capacity' => 20,
            'departure_time' => '06:00',
            'return_time' => '22:00',
        ])->assertOk()->json('data.id');

        $horarios = HorarioViagem::where('linha_id', $id)->orderBy('horario_saida')->get();
        $this->assertSame([HorarioViagem::IDA, HorarioViagem::VOLTA], $horarios->pluck('sentido')->all());
        $this->assertSame([1, 2, 3, 4, 5], array_map('intval', $horarios->first()->dias_semana));
    }

    public function test_alterar_o_horario_na_linha_chega_as_viagens(): void
    {
        $this->travelTo($this->local('2026-10-19 09:00:00'));
        $linha = Linha::factory()->create(['departure_time' => '17:30:00', 'return_time' => '22:30:00']);
        $this->horario($linha, HorarioViagem::IDA, '17:30:00');
        Sanctum::actingAs($this->usuario('gestor'));
        $this->postJson('/api/viagens/gerar', ['de' => '2026-10-19', 'ate' => '2026-10-19'])->assertOk();

        $this->patchJson("/api/linha/{$linha->id}", ['name' => $linha->name, 'max_capacity' => 40, 'departure_time' => '18:00', 'return_time' => '22:30'])
            ->assertOk()
            ->assertJsonMissingPath('aviso');

        $this->assertStringStartsWith('18:00', HorarioViagem::where('linha_id', $linha->id)->where('sentido', HorarioViagem::IDA)->sole()->horario_saida);

        $this->postJson('/api/viagens/gerar', ['de' => '2026-10-19', 'ate' => '2026-10-19'])->assertOk();
        $this->assertStringStartsWith('18:00', Viagem::where('linha_id', $linha->id)->where('sentido', HorarioViagem::IDA)->sole()->horario_saida);
    }

    public function test_alterar_o_horario_na_programacao_atualiza_a_linha(): void
    {
        $linha = Linha::factory()->create(['departure_time' => '06:00:00']);
        $horario = $this->horario($linha, HorarioViagem::IDA, '06:00:00');
        Sanctum::actingAs($this->usuario('gestor'));

        $this->patchJson("/api/horarios-viagem/{$horario->id}", ['horario_saida' => '06:45'])->assertOk();

        $this->assertStringStartsWith('06:45', $linha->refresh()->departure_time);
    }

    public function test_com_varios_horarios_no_sentido_a_linha_avisa_e_nao_escolhe_um(): void
    {
        $linha = Linha::factory()->create(['departure_time' => '06:00:00']);
        $this->horario($linha, HorarioViagem::IDA, '06:00:00');
        $this->horario($linha, HorarioViagem::IDA, '07:00:00');
        Sanctum::actingAs($this->usuario('gestor'));

        $this->patchJson("/api/linha/{$linha->id}", ['name' => $linha->name, 'max_capacity' => 40, 'departure_time' => '08:00'])
            ->assertOk()
            ->assertJsonPath('aviso', 'A linha tem mais de um horário em: Ida. Ajuste-os na programação.')
            ->assertJsonPath('data.departure_time', '06:00:00');

        $this->assertSame(0, HorarioViagem::where('linha_id', $linha->id)->where('horario_saida', 'like', '08:00%')->count());
    }

    public function test_carga_inicial_prepara_o_que_falta_e_pode_rodar_de_novo(): void
    {
        $this->travelTo($this->local('2026-10-19 09:00:00'));
        $linha = Linha::factory()->create(['departure_time' => '06:00:00', 'return_time' => '22:00:00']);

        $primeira = app(CargaInicialPresenca::class)->executar(criarVans: true)[0];

        $this->assertSame(2, $primeira['horarios_criados']);
        $this->assertSame("Van da {$linha->name}", $primeira['van']);
        $this->assertStringContainsString('/presenca/scan?t=', $primeira['qr_url']);
        $this->assertSame(0, $primeira['pontos']);
        $this->assertGreaterThan(0, Viagem::where('linha_id', $linha->id)->count());

        $segunda = app(CargaInicialPresenca::class)->executar(criarVans: true)[0];

        $this->assertSame(0, $segunda['horarios_criados']);
        $this->assertNull($segunda['qr_url']);
        $this->assertSame(2, HorarioViagem::where('linha_id', $linha->id)->count());
    }

    public function test_carga_inicial_sem_a_opcao_nao_cria_van(): void
    {
        Linha::factory()->create(['departure_time' => '06:00:00', 'return_time' => null]);

        $situacao = app(CargaInicialPresenca::class)->executar()[0];

        $this->assertSame(1, $situacao['horarios_criados']);
        $this->assertNull($situacao['van']);
    }
}
