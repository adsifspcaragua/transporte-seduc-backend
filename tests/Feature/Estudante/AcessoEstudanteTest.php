<?php

namespace Tests\Feature\Estudante;

use App\Models\Estudante;
use App\Models\Inscricao;
use App\Models\InscricaoInstituicoes;
use App\Models\PeriodoReecadastro;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcessoEstudanteTest extends TestCase
{
    use RefreshDatabase;

    public function test_cpf_novo_abre_o_fluxo_de_inscricao(): void
    {
        $this->postJson('/api/area-estudante/acesso', ['cpf' => '12345678901'])
            ->assertOk()
            ->assertJsonPath('fluxo', 'inscricao')
            ->assertJsonPath('data.cpf', '12345678901');
    }

    public function test_inscricao_existente_volta_preenchida_com_novo_token(): void
    {
        $inscricao = Inscricao::factory()->create(['status' => 'Incompleto']);
        InscricaoInstituicoes::factory()->create([
            'inscricao_id' => $inscricao->id,
            'days_of_week' => [1, 3, 5],
        ]);

        $this->postJson('/api/area-estudante/acesso', ['cpf' => $inscricao->cpf])
            ->assertOk()
            ->assertJsonPath('fluxo', 'lista_espera')
            ->assertJsonPath('data.inscricao.id', $inscricao->id)
            ->assertJsonPath('data.instituicao.days_of_week', [1, 3, 5])
            ->assertJsonStructure(['data' => ['inscricao' => ['token']]]);
    }

    public function test_aluno_ativo_com_periodo_aberto_entra_no_recadastro_com_dados(): void
    {
        PeriodoReecadastro::factory()->aberto()->create();
        $estudante = Estudante::factory()->create();
        InscricaoInstituicoes::factory()->create([
            'inscricao_id' => $estudante->inscricao_id,
            'days_of_week' => [1, 2, 4],
        ]);

        $this->postJson('/api/area-estudante/acesso', ['cpf' => $estudante->cpf])
            ->assertOk()
            ->assertJsonPath('fluxo', 'recadastro')
            ->assertJsonPath('data.cadastro.name', $estudante->inscricao->name)
            ->assertJsonPath('data.cadastro.days_of_week', [1, 2, 4])
            ->assertJsonStructure(['data' => ['token', 'cadastro', 'documentos']]);
    }

    public function test_aluno_atualiza_os_mesmos_dados_do_cadastro_durante_o_recadastro(): void
    {
        PeriodoReecadastro::factory()->aberto()->create();
        $estudante = Estudante::factory()->create();
        $dadosInstitucionais = InscricaoInstituicoes::factory()->create([
            'inscricao_id' => $estudante->inscricao_id,
            'days_of_week' => [1, 2, 4],
        ]);
        $acesso = $this->postJson('/api/area-estudante/acesso', ['cpf' => $estudante->cpf])
            ->assertOk()
            ->json('data');

        $this->putJson("/api/reecadastro/solicitacoes/{$acesso['solicitacao_id']}/dados", [
            'token' => $acesso['token'],
            'name' => 'Aluno Atualizado',
            'rg' => '12345678',
            'mother_name' => 'Maria da Silva',
            'father_name' => null,
            'birth_date' => '2004-05-10',
            'phone' => '12999999999',
            'email' => 'aluno.atualizado@example.com',
            'cep' => '11660000',
            'address' => 'Rua Atualizada',
            'neighborhood' => 'Centro',
            'complement' => null,
            'city' => 'Caraguatatuba',
            'number' => 120,
            'course' => 'Engenharia Civil',
            'semester' => '5',
            'expected_completion' => '2028-12-10',
            'instituicao_id' => $dadosInstitucionais->instituicao_id,
            'shift' => 2,
            'city_destination' => 'São Sebastião',
            'used_transport' => true,
            'days_of_week' => [1, 3, 5],
            'has_scholarship' => false,
            'scholarship_type' => null,
        ])->assertOk()->assertJsonPath('data.cadastro.name', 'Aluno Atualizado');

        $this->assertDatabaseHas('inscricoes', [
            'id' => $estudante->inscricao_id,
            'name' => 'Aluno Atualizado',
            'cep' => '11660000',
        ]);
        $this->assertDatabaseHas('inscricao_instituicoes', [
            'inscricao_id' => $estudante->inscricao_id,
            'semester' => '5',
            'shift' => 2,
        ]);
    }
}
