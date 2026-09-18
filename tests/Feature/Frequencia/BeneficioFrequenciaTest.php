<?php

namespace Tests\Feature\Frequencia;

use App\Mail\Frequencia\AvisoFaltasMail;
use App\Mail\Frequencia\BeneficioPerdidoMail;
use App\Models\AvisoFrequencia;
use App\Models\Chamada;
use App\Models\Estudante;
use App\Models\Frequencia;
use App\Models\Justificativa;
use App\Models\Linha;
use App\Models\Role;
use App\Models\User;
use App\Services\Frequencia\BeneficioFrequenciaService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * TESTE FUNCIONAL da regra de permanencia no beneficio.
 *
 * Perde o beneficio quem, no mesmo mes, tiver 3 faltas seguidas ou 5 no total.
 * A contagem recomeca a cada mes. Uma falta antes do limite, o estudante e
 * avisado por e-mail.
 *
 * Classes de equivalencia e valores-limite: 2 seguidas (aviso) / 3 seguidas
 * (perda); 4 no mes (aviso) / 5 no mes (perda).
 */
class BeneficioFrequenciaTest extends TestCase
{
    use RefreshDatabase;

    /** Quarta-feira. */
    private const HOJE = '2026-09-09';

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(Carbon::parse(self::HOJE.' 08:00:00'));
        $this->seed(RolePermissionSeeder::class);
        Mail::fake();
    }

    private function usuario(string $perfil): User
    {
        $user = User::factory()->create(['ativo' => true]);
        $user->roles()->sync(Role::where('title', $perfil)->pluck('id'));

        return $user;
    }

    /**
     * Uma chamada por dia a partir de $inicio, na ordem das situacoes.
     *
     * @param  list<string>  $situacoes
     */
    private function historico(Estudante $estudante, array $situacoes, string $inicio = '2026-09-01', string $status = Chamada::FECHADA): void
    {
        foreach ($situacoes as $dia => $situacao) {
            $chamada = Chamada::firstOrCreate(
                ['linha_id' => $estudante->linha_id, 'data' => Carbon::parse($inicio)->addDays($dia)->toDateString()],
                ['status' => $status],
            );

            Frequencia::factory()->create([
                'chamada_id' => $chamada->id,
                'estudante_id' => $estudante->id,
                'situacao' => $situacao,
            ]);
        }
    }

    private function estudante(): Estudante
    {
        return Estudante::factory()->create(['linha_id' => Linha::factory(), 'status' => 'Ativo']);
    }

    private function avaliar(Estudante $estudante): void
    {
        app(BeneficioFrequenciaService::class)->avaliar([$estudante->id], today());
    }

    public function test_duas_faltas_seguidas_geram_aviso(): void
    {
        $estudante = $this->estudante();
        $this->historico($estudante, ['Presente', 'Falta', 'Falta']);

        $this->avaliar($estudante);

        $this->assertSame('Ativo', $estudante->refresh()->status);
        Mail::assertQueued(AvisoFaltasMail::class, fn (AvisoFaltasMail $mail) => $mail->hasTo($estudante->email)
            && $mail->motivos === [AvisoFrequencia::PERTO_DO_LIMITE_SEGUIDAS]);
        Mail::assertNotQueued(BeneficioPerdidoMail::class);
    }

    public function test_tres_faltas_seguidas_retiram_o_beneficio(): void
    {
        $estudante = $this->estudante();
        $this->historico($estudante, ['Presente', 'Falta', 'Falta', 'Falta']);

        $this->avaliar($estudante);

        $this->assertSame('Inativo', $estudante->refresh()->status);
        Mail::assertQueued(BeneficioPerdidoMail::class, fn (BeneficioPerdidoMail $mail) => $mail->hasTo($estudante->email));
        $this->assertDatabaseHas('avisos_frequencia', [
            'estudante_id' => $estudante->id,
            'referencia' => '2026-09',
            'tipo' => AvisoFrequencia::BENEFICIO_PERDIDO,
            'faltas_no_mes' => 3,
            'faltas_seguidas' => 3,
        ]);
    }

    public function test_quatro_faltas_intercaladas_geram_aviso_do_mes(): void
    {
        $estudante = $this->estudante();
        $this->historico($estudante, ['Falta', 'Presente', 'Falta', 'Presente', 'Falta', 'Presente', 'Falta']);

        $this->avaliar($estudante);

        $this->assertSame('Ativo', $estudante->refresh()->status);
        Mail::assertQueued(AvisoFaltasMail::class, fn (AvisoFaltasMail $mail) => $mail->motivos === [AvisoFrequencia::PERTO_DO_LIMITE_NO_MES]);
    }

    public function test_cinco_faltas_intercaladas_retiram_o_beneficio(): void
    {
        $estudante = $this->estudante();
        $this->historico($estudante, ['Falta', 'Presente', 'Falta', 'Presente', 'Falta', 'Presente', 'Falta', 'Presente', 'Falta']);

        $this->avaliar($estudante);

        $this->assertSame('Inativo', $estudante->refresh()->status);
        Mail::assertQueued(BeneficioPerdidoMail::class);
    }

    public function test_uma_falta_nao_gera_nada(): void
    {
        $estudante = $this->estudante();
        $this->historico($estudante, ['Presente', 'Falta', 'Presente']);

        $this->avaliar($estudante);

        $this->assertSame('Ativo', $estudante->refresh()->status);
        Mail::assertNothingQueued();
    }

    public function test_a_contagem_recomeca_a_cada_mes(): void
    {
        $estudante = $this->estudante();
        // Quatro faltas em agosto, as duas ultimas encostadas em setembro.
        $this->historico($estudante, ['Falta', 'Falta', 'Falta', 'Falta'], '2026-08-28');
        // Mais uma no dia 1: seria a 3a seguida e a 5a no total, se nao zerasse.
        $this->historico($estudante, ['Falta']);

        $this->avaliar($estudante);

        $this->assertSame('Ativo', $estudante->refresh()->status);
        Mail::assertNothingQueued();
    }

    public function test_justificada_nao_conta_e_interrompe_a_sequencia(): void
    {
        $estudante = $this->estudante();
        $this->historico($estudante, ['Falta', 'Justificada', 'Falta', 'Justificada', 'Falta', 'Justificada', 'Falta']);

        $this->avaliar($estudante);

        // 4 faltas: aviso do mes, mas nenhuma sequencia.
        $this->assertSame('Ativo', $estudante->refresh()->status);
        Mail::assertQueued(AvisoFaltasMail::class, fn (AvisoFaltasMail $mail) => $mail->motivos === [AvisoFrequencia::PERTO_DO_LIMITE_NO_MES]);
    }

    public function test_pendente_nao_interrompe_a_sequencia(): void
    {
        $estudante = $this->estudante();
        $this->historico($estudante, ['Falta', 'Pendente', 'Falta', 'Falta']);

        $this->avaliar($estudante);

        $this->assertSame('Inativo', $estudante->refresh()->status);
    }

    public function test_folha_aberta_ainda_nao_conta(): void
    {
        $estudante = $this->estudante();
        $this->historico($estudante, ['Falta', 'Falta']);
        $this->historico($estudante, ['Falta'], '2026-09-03', Chamada::ABERTA);

        $this->avaliar($estudante);

        $this->assertSame('Ativo', $estudante->refresh()->status);
    }

    public function test_o_mesmo_aviso_nao_e_enviado_duas_vezes_no_mes(): void
    {
        $estudante = $this->estudante();
        $this->historico($estudante, ['Falta', 'Falta']);

        $this->avaliar($estudante);
        $this->avaliar($estudante);

        Mail::assertQueuedCount(1);
    }

    public function test_estudante_inativo_nao_e_avaliado(): void
    {
        $estudante = $this->estudante();
        $estudante->update(['status' => 'Inativo']);
        $this->historico($estudante, ['Falta', 'Falta', 'Falta']);

        $this->avaliar($estudante);

        Mail::assertNothingQueued();
        $this->assertSame(0, AvisoFrequencia::count());
    }

    public function test_fechar_a_chamada_aplica_a_regra(): void
    {
        $motorista = $this->usuario('motorista');
        $linha = Linha::factory()->create(['motorista_id' => $motorista->id]);
        $estudante = Estudante::factory()->create(['linha_id' => $linha->id, 'days_of_week' => [1, 2, 3]]);
        // Segunda e terca desta semana.
        $this->historico($estudante, ['Falta', 'Falta'], '2026-09-07');

        Sanctum::actingAs($motorista);

        $chamadaId = $this->postJson('/api/frequencias/chamadas', ['linha_id' => $linha->id, 'data' => self::HOJE])->json('data.id');
        $this->putJson("/api/frequencias/chamadas/{$chamadaId}", [
            'frequencias' => [['estudante_id' => $estudante->id, 'situacao' => 'Falta']],
        ])->assertOk();

        // Marcar nao basta: a folha aberta ainda pode ser corrigida.
        $this->assertSame('Ativo', $estudante->refresh()->status);

        $this->patchJson("/api/frequencias/chamadas/{$chamadaId}/fechar")->assertOk();

        $this->assertSame('Inativo', $estudante->refresh()->status);
        Mail::assertQueued(BeneficioPerdidoMail::class);
    }

    public function test_rejeitar_a_justificativa_reaplica_a_regra(): void
    {
        $estudante = $this->estudante();
        $this->historico($estudante, ['Falta', 'Falta', 'Justificada']);
        $justificativa = Justificativa::create([
            'frequencia_id' => Frequencia::where('situacao', 'Justificada')->value('id'),
            'motivo' => 'Consulta médica',
        ]);

        Sanctum::actingAs($this->usuario('gestor'));

        $this->putJson("/api/frequencias/justificativas/{$justificativa->id}/analise", [
            'decisao' => 'Rejeitada',
            'parecer' => 'Sem comprovante.',
        ])->assertOk();

        $this->assertSame('Inativo', $estudante->refresh()->status);
        Mail::assertQueued(BeneficioPerdidoMail::class);
    }

    public function test_aprovar_a_justificativa_mantem_a_falta_retirada(): void
    {
        $estudante = $this->estudante();
        $this->historico($estudante, ['Falta', 'Falta', 'Justificada']);
        $justificativa = Justificativa::create([
            'frequencia_id' => Frequencia::where('situacao', 'Justificada')->value('id'),
            'motivo' => 'Atestado médico',
        ]);

        Sanctum::actingAs($this->usuario('gestor'));

        $this->putJson("/api/frequencias/justificativas/{$justificativa->id}/analise", ['decisao' => 'Aprovada'])->assertOk();

        $this->assertSame('Ativo', $estudante->refresh()->status);
        $this->assertSame(2, app(BeneficioFrequenciaService::class)->situacao($estudante, today())['faltas_no_mes']);
        Mail::assertNotQueued(BeneficioPerdidoMail::class);
    }

    public function test_quem_perde_o_beneficio_sai_da_lista_do_motorista(): void
    {
        $motorista = $this->usuario('motorista');
        $linha = Linha::factory()->create(['motorista_id' => $motorista->id]);
        $perdeu = Estudante::factory()->create(['linha_id' => $linha->id, 'name' => 'Perdeu']);
        $segue = Estudante::factory()->create(['linha_id' => $linha->id, 'name' => 'Segue']);
        $this->historico($perdeu, ['Falta', 'Falta', 'Falta']);

        $this->avaliar($perdeu);

        Sanctum::actingAs($motorista);

        $naFolha = $this->postJson('/api/frequencias/chamadas', ['linha_id' => $linha->id, 'data' => self::HOJE])
            ->assertCreated()
            ->json('data.frequencias');

        $this->assertSame([$segue->id], array_column($naFolha, 'estudante_id'));
    }

    public function test_email_de_aviso_mostra_os_numeros_e_as_datas(): void
    {
        $estudante = $this->estudante();
        $this->historico($estudante, ['Presente', 'Falta', 'Falta']);
        $situacao = app(BeneficioFrequenciaService::class)->situacao($estudante, today());

        $mail = new AvisoFaltasMail($estudante, $situacao, [AvisoFrequencia::PERTO_DO_LIMITE_SEGUIDAS]);

        $mail->assertSeeInHtml($estudante->name);
        $mail->assertSeeInHtml('setembro de 2026');
        $mail->assertSeeInHtml('02/09/2026, 03/09/2026');
        $mail->assertSeeInText('2 faltas seguidas');
    }

    public function test_relatorio_do_estudante_mostra_a_situacao_no_mes(): void
    {
        $estudante = $this->estudante();
        $this->historico($estudante, ['Falta', 'Presente', 'Falta', 'Falta']);

        Sanctum::actingAs($this->usuario('gestor'));

        $this->getJson("/api/frequencias/estudantes/{$estudante->id}/relatorio")
            ->assertOk()
            ->assertJsonPath('data.beneficio.faltas_no_mes', 3)
            ->assertJsonPath('data.beneficio.sequencia_atual', 2)
            ->assertJsonPath('data.beneficio.limite_no_mes', 5)
            ->assertJsonPath('data.beneficio.limite_seguidas', 3);
    }
}
