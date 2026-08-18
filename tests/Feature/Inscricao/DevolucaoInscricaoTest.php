<?php

namespace Tests\Feature\Inscricao;

use App\Models\Estudante;
use App\Models\Inscricao;
use App\Models\InscricaoDocumento;
use App\Models\InscricaoInstituicoes;
use App\Models\Role;
use App\Models\User;
use App\Services\Inscricao\InscricaoStatusService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * TESTE FUNCIONAL (caixa-preta) da devolucao da inscricao para correcao.
 *
 * Rejeitar e definitivo: o CPF fica preso aquela inscricao e o estudante nao
 * consegue mais entrar nem abrir outra. Isso serve para quem nao tem direito ao
 * beneficio, mas nao para um comprovante ilegivel.
 *
 * Devolver reabre a inscricao com o motivo, mantendo o CPF e o que ja foi
 * preenchido, e exige de volta so os documentos que a responsavel apontou.
 */
class DevolucaoInscricaoTest extends TestCase
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

        foreach (InscricaoDocumento::OBRIGATORIOS as $nome) {
            InscricaoDocumento::create([
                'inscricao_id' => $inscricao->id,
                'name' => $nome,
                'type' => 'documento',
                'file_path' => "inscricoes/{$inscricao->id}/{$nome}.pdf",
                'status' => 'Em analise',
            ]);
        }

        return $inscricao;
    }

    public function test_devolucao_reabre_a_inscricao_com_o_motivo(): void
    {
        $this->autenticarComo('gestor');
        $inscricao = $this->inscricaoCompleta();

        $this->putJson("/api/inscricoes/analise/{$inscricao->id}", [
            'decisao' => 'Devolvido',
            'motivo' => 'Comprovante de residencia ilegivel.',
        ])->assertOk();

        $inscricao->refresh();

        $this->assertSame('Incompleto', $inscricao->status);
        $this->assertSame('Comprovante de residencia ilegivel.', $inscricao->observation);
    }

    public function test_devolucao_permite_o_estudante_voltar_pelo_cpf(): void
    {
        $this->autenticarComo('gestor');
        $inscricao = $this->inscricaoCompleta();

        $this->putJson("/api/inscricoes/analise/{$inscricao->id}", [
            'decisao' => 'Devolvido',
            'motivo' => 'Faltou o historico legivel.',
        ])->assertOk();

        // O estudante nao faz login: volta pela area do estudante com o CPF.
        $resposta = $this->postJson('/api/area-estudante/acesso', ['cpf' => $inscricao->cpf])
            ->assertOk()
            ->json();

        $this->assertSame('lista_espera', $resposta['fluxo']);
        $this->assertTrue($resposta['data']['pode_editar']);
        $this->assertSame('Faltou o historico legivel.', $resposta['data']['inscricao']['observation']);
    }

    public function test_devolucao_pede_de_volta_so_os_documentos_apontados(): void
    {
        $this->autenticarComo('gestor');
        $inscricao = $this->inscricaoCompleta();

        $this->putJson("/api/inscricoes/analise/{$inscricao->id}", [
            'decisao' => 'Devolvido',
            'motivo' => 'Comprovante de residencia ilegivel.',
            'documentos' => ['residencia'],
        ])->assertOk();

        $documentos = $inscricao->inscricao_documentos()->pluck('status', 'name');

        $this->assertSame('Rejeitado', $documentos['residencia']);
        $this->assertSame('Em analise', $documentos['identidade']);
        $this->assertSame('Em analise', $documentos['historico']);
    }

    public function test_devolucao_nao_cria_estudante(): void
    {
        $this->autenticarComo('gestor');
        $inscricao = $this->inscricaoCompleta();

        $this->putJson("/api/inscricoes/analise/{$inscricao->id}", [
            'decisao' => 'Devolvido',
            'motivo' => 'Dados de endereco incompletos.',
        ])->assertOk();

        $this->assertNull(Estudante::where('inscricao_id', $inscricao->id)->first());
    }

    public function test_devolucao_exige_o_motivo(): void
    {
        $this->autenticarComo('gestor');
        $inscricao = $this->inscricaoCompleta();

        $this->putJson("/api/inscricoes/analise/{$inscricao->id}", ['decisao' => 'Devolvido'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('motivo');
    }

    public function test_documento_desconhecido_na_lista_de_reenvio_e_recusado(): void
    {
        $this->autenticarComo('gestor');
        $inscricao = $this->inscricaoCompleta();

        $this->putJson("/api/inscricoes/analise/{$inscricao->id}", [
            'decisao' => 'Devolvido',
            'motivo' => 'Documento invalido.',
            'documentos' => ['passaporte'],
        ])->assertStatus(422)->assertJsonValidationErrors('documentos.0');
    }

    public function test_devolucao_aponta_os_campos_para_corrigir(): void
    {
        $this->autenticarComo('gestor');
        $inscricao = $this->inscricaoCompleta();

        $this->putJson("/api/inscricoes/analise/{$inscricao->id}", [
            'decisao' => 'Devolvido',
            'motivo' => 'O nome esta errado e falta o numero da casa.',
            'campos' => ['name', 'number'],
        ])->assertOk();

        $this->assertSame(['name', 'number'], $inscricao->refresh()->campos_pendentes);

        // O estudante volta pelo CPF e ve os campos com o rotulo pronto.
        $campos = $this->postJson('/api/area-estudante/acesso', ['cpf' => $inscricao->cpf])
            ->assertOk()
            ->json('data.inscricao.campos_pendentes');

        $this->assertSame(
            [
                ['campo' => 'name', 'label' => 'Nome completo'],
                ['campo' => 'number', 'label' => 'Número'],
            ],
            $campos,
        );
    }

    public function test_reenvio_do_estudante_encerra_a_pendencia(): void
    {
        $this->autenticarComo('gestor');
        $inscricao = $this->inscricaoCompleta();

        $this->putJson("/api/inscricoes/analise/{$inscricao->id}", [
            'decisao' => 'Devolvido',
            'motivo' => 'O nome esta errado.',
            'campos' => ['name'],
        ])->assertOk();

        // O estudante corrige e reenvia: a inscricao volta completa para a fila e
        // o aviso de correcao precisa sumir, senao ele o veria para sempre.
        app(InscricaoStatusService::class)
            ->refreshStatus($inscricao->refresh());

        $inscricao->refresh();

        $this->assertSame('Em analise', $inscricao->status);
        $this->assertNull($inscricao->campos_pendentes);
    }

    public function test_cpf_nao_pode_ser_apontado_para_correcao(): void
    {
        $this->autenticarComo('gestor');
        $inscricao = $this->inscricaoCompleta();

        // O CPF identifica a inscricao e e unico: trocar de CPF e comecar outra.
        $this->putJson("/api/inscricoes/analise/{$inscricao->id}", [
            'decisao' => 'Devolvido',
            'motivo' => 'Tentativa invalida.',
            'campos' => ['cpf'],
        ])->assertStatus(422)->assertJsonValidationErrors('campos.0');
    }

    public function test_aprovacao_limpa_os_campos_pendentes(): void
    {
        $this->autenticarComo('gestor');
        $inscricao = $this->inscricaoCompleta();
        $inscricao->update(['campos_pendentes' => ['name']]);

        $this->putJson("/api/inscricoes/analise/{$inscricao->id}", ['decisao' => 'Aprovado'])
            ->assertOk();

        $this->assertNull($inscricao->refresh()->campos_pendentes);
    }

    public function test_rejeicao_continua_barrando_o_estudante(): void
    {
        $this->autenticarComo('gestor');
        $inscricao = $this->inscricaoCompleta();

        $this->putJson("/api/inscricoes/analise/{$inscricao->id}", [
            'decisao' => 'Rejeitado',
            'motivo' => 'Nao atende aos criterios.',
        ])->assertOk();

        $this->assertSame('Rejeitado', $inscricao->refresh()->status);

        $this->postJson('/api/area-estudante/acesso', ['cpf' => $inscricao->cpf])
            ->assertStatus(409);
    }
}
