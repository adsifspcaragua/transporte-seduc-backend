<?php

namespace Tests\Feature\Estudante;

use App\Mail\Acesso\CodigoAcessoMail;
use App\Models\CodigoAcesso;
use App\Models\Estudante;
use App\Models\Inscricao;
use App\Models\RegistroAuditoria;
use App\Models\User;
use App\Services\Estudante\CodigoAcessoService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Laravel\Sanctum\Sanctum;
use Tests\Support\CenarioPresenca;
use Tests\TestCase;

/**
 * TESTE FUNCIONAL da correcao de e-mail pelo atendimento.
 *
 * Esse endereco libera o acesso por CPF e o link de senha, entao a correcao
 * precisa de justificativa, deixa rastro e invalida o que foi enviado ao antigo.
 */
class CorrecaoEmailTest extends TestCase
{
    use CenarioPresenca, RefreshDatabase;

    private const JUSTIFICATIVA = 'RG conferido presencialmente no atendimento.';

    private User $gestor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        $this->gestor = $this->usuario('gestor');
        Sanctum::actingAs($this->gestor);
    }

    private function estudanteComContaEEmail(string $email = 'antigo@example.com'): Estudante
    {
        $conta = User::factory()->create(['email' => $email]);
        $estudante = Estudante::factory()->create(['email' => $email, 'user_id' => $conta->id]);
        $estudante->inscricao->update(['email' => $email]);

        return $estudante;
    }

    public function test_corrige_cadastro_inscricao_e_conta(): void
    {
        $estudante = $this->estudanteComContaEEmail();

        $this->patchJson("/api/estudantes/{$estudante->id}/email", ['email' => 'novo@example.com', 'justificativa' => self::JUSTIFICATIVA])
            ->assertOk()
            ->assertJsonPath('data.conta_atualizada', true);

        $this->assertSame('novo@example.com', $estudante->refresh()->email);
        $this->assertSame('novo@example.com', $estudante->inscricao->refresh()->email);
        $this->assertSame('novo@example.com', $estudante->user->refresh()->email);
    }

    public function test_exige_justificativa_e_email_valido(): void
    {
        $estudante = $this->estudanteComContaEEmail();

        $this->patchJson("/api/estudantes/{$estudante->id}/email", ['email' => 'novo@example.com'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('justificativa');

        $this->patchJson("/api/estudantes/{$estudante->id}/email", ['email' => 'nao-e-email', 'justificativa' => self::JUSTIFICATIVA])
            ->assertStatus(422)
            ->assertJsonValidationErrors('email');

        $this->patchJson("/api/estudantes/{$estudante->id}/email", ['email' => 'antigo@example.com', 'justificativa' => self::JUSTIFICATIVA])
            ->assertStatus(422)
            ->assertJsonValidationErrors('email');
    }

    public function test_codigo_enviado_ao_endereco_antigo_deixa_de_valer(): void
    {
        Mail::fake();
        $estudante = $this->estudanteComContaEEmail();
        $codigoAntigo = app(CodigoAcessoService::class)->gerar($estudante->cpf);

        $this->patchJson("/api/estudantes/{$estudante->id}/email", ['email' => 'novo@example.com', 'justificativa' => self::JUSTIFICATIVA])->assertOk();

        $this->postJson('/api/area-estudante/acesso/verificar', ['cpf' => $estudante->cpf, 'codigo' => $codigoAntigo])->assertStatus(422);
    }

    public function test_novo_codigo_sai_na_hora_para_o_endereco_novo_mesmo_depois_do_limite(): void
    {
        Mail::fake();
        $estudante = $this->estudanteComContaEEmail();

        foreach (range(1, 5) as $_) {
            app(CodigoAcessoService::class)->gerar($estudante->cpf);
        }

        $this->postJson('/api/area-estudante/acesso', ['cpf' => $estudante->cpf])->assertStatus(200);
        $this->travel(61)->seconds();
        $this->postJson('/api/area-estudante/acesso', ['cpf' => $estudante->cpf])->assertStatus(429);

        $this->patchJson("/api/estudantes/{$estudante->id}/email", ['email' => 'novo@example.com', 'justificativa' => self::JUSTIFICATIVA])->assertOk();

        $this->postJson('/api/area-estudante/acesso', ['cpf' => $estudante->cpf])
            ->assertOk()
            ->assertJsonPath('data.email_mascarado', 'no***@e***.com');

        Mail::assertQueued(CodigoAcessoMail::class, fn (CodigoAcessoMail $mail) => $mail->hasTo('novo@example.com'));
        $this->assertSame(1, CodigoAcesso::where('cpf', $estudante->cpf)->count());
    }

    public function test_link_de_senha_do_endereco_antigo_deixa_de_valer(): void
    {
        $estudante = $this->estudanteComContaEEmail();
        Password::createToken($estudante->user);
        $this->assertSame(1, DB::table('password_reset_tokens')->where('email', 'antigo@example.com')->count());

        $this->patchJson("/api/estudantes/{$estudante->id}/email", ['email' => 'novo@example.com', 'justificativa' => self::JUSTIFICATIVA])->assertOk();

        $this->assertSame(0, DB::table('password_reset_tokens')->where('email', 'antigo@example.com')->count());
    }

    public function test_alterar_o_cadastro_pela_rota_comum_tambem_atualiza_a_conta(): void
    {
        $estudante = $this->estudanteComContaEEmail();

        $this->putJson("/api/estudantes/{$estudante->id}", ['email' => 'comum@example.com'])->assertOk();

        $this->assertSame('comum@example.com', $estudante->user->refresh()->email);
    }

    public function test_rota_comum_recusa_email_de_outra_conta(): void
    {
        $estudante = $this->estudanteComContaEEmail();
        User::factory()->create(['email' => 'equipe@example.com']);

        $this->putJson("/api/estudantes/{$estudante->id}", ['email' => 'equipe@example.com'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('email');
    }

    public function test_nao_aceita_email_de_outra_pessoa(): void
    {
        $estudante = $this->estudanteComContaEEmail();
        Estudante::factory()->create(['email' => 'outro.estudante@example.com']);
        Inscricao::factory()->create(['email' => 'outra.inscricao@example.com']);
        User::factory()->create(['email' => 'equipe@example.com']);

        foreach (['outro.estudante@example.com', 'outra.inscricao@example.com', 'equipe@example.com'] as $email) {
            $this->patchJson("/api/estudantes/{$estudante->id}/email", ['email' => $email, 'justificativa' => self::JUSTIFICATIVA])
                ->assertStatus(422)
                ->assertJsonValidationErrors('email');
        }

        $this->assertSame('antigo@example.com', $estudante->refresh()->email);
    }

    public function test_inscricao_na_fila_e_corrigida_em_qualquer_situacao(): void
    {
        Mail::fake();
        $inscricao = Inscricao::factory()->create(['status' => 'Em analise', 'email' => 'fila@example.com']);
        $codigoAntigo = app(CodigoAcessoService::class)->gerar($inscricao->cpf);

        $this->patchJson("/api/inscricoes/{$inscricao->id}/email", ['email' => 'fila.nova@example.com', 'justificativa' => self::JUSTIFICATIVA])
            ->assertOk()
            ->assertJsonPath('data.estudante_id', null)
            ->assertJsonPath('data.conta_atualizada', false);

        $this->assertSame('fila.nova@example.com', $inscricao->refresh()->email);
        $this->postJson('/api/area-estudante/acesso/verificar', ['cpf' => $inscricao->cpf, 'codigo' => $codigoAntigo])->assertStatus(422);
    }

    public function test_inscricao_de_quem_ja_e_estudante_atualiza_os_dois(): void
    {
        $estudante = $this->estudanteComContaEEmail();

        $this->patchJson("/api/inscricoes/{$estudante->inscricao_id}/email", ['email' => 'novo@example.com', 'justificativa' => self::JUSTIFICATIVA])->assertOk();

        $this->assertSame('novo@example.com', $estudante->refresh()->email);
        $this->assertSame('novo@example.com', $estudante->user->refresh()->email);
    }

    public function test_auditoria_tem_responsavel_e_justificativa_sem_o_endereco_completo(): void
    {
        $estudante = $this->estudanteComContaEEmail();

        $this->patchJson("/api/estudantes/{$estudante->id}/email", ['email' => 'novo@example.com', 'justificativa' => self::JUSTIFICATIVA])->assertOk();

        $registro = RegistroAuditoria::where('acao', 'email.corrigir')->sole();

        $this->assertSame($this->gestor->id, $registro->user_id);
        $this->assertSame(self::JUSTIFICATIVA, $registro->justificativa);
        $this->assertSame('no***@e***.com', $registro->dados['depois']);
        $this->assertStringNotContainsString('novo@example.com', json_encode($registro->dados));
        $this->assertStringNotContainsString('antigo@example.com', json_encode($registro->dados));
    }

    public function test_permissoes(): void
    {
        $estudante = $this->estudanteComContaEEmail();
        $inscricao = Inscricao::factory()->create(['status' => 'Em analise']);
        $dados = ['email' => 'novo@example.com', 'justificativa' => self::JUSTIFICATIVA];

        // Operador atende quem ja e estudante, mas nao mexe na fila de analise.
        Sanctum::actingAs($this->usuario('operador'));
        $this->patchJson("/api/inscricoes/{$inscricao->id}/email", $dados)->assertForbidden();
        $this->patchJson("/api/estudantes/{$estudante->id}/email", $dados)->assertOk();

        foreach (['motorista', 'estudante'] as $perfil) {
            Sanctum::actingAs($this->usuario($perfil));
            $this->patchJson("/api/estudantes/{$estudante->id}/email", ['email' => 'outro@example.com', 'justificativa' => self::JUSTIFICATIVA])->assertForbidden();
        }

        auth()->forgetGuards();
        $this->app['auth']->forgetGuards();
        $this->patchJson("/api/inscricoes/{$inscricao->id}/email", $dados)->assertUnauthorized();
    }
}
