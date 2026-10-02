<?php

namespace Tests\Feature\Estudante;

use App\Models\Estudante;
use App\Models\InscricaoInstituicoes;
use App\Models\Instituicao;
use App\Models\Linha;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ExportacaoEstudantesTest extends TestCase
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

    private function estudante(array $dados = []): Estudante
    {
        $estudante = Estudante::factory()->create([
            'name' => 'Ana Conceição',
            'cpf' => '12345678900',
            'phone' => '12981234567',
            'instituicao_id' => Instituicao::factory()->create(['name' => 'FATEC Caraguatatuba']),
            'linha_id' => Linha::factory()->create(['name' => 'Linha Centro']),
            ...$dados,
        ]);
        InscricaoInstituicoes::factory()->create([
            'inscricao_id' => $estudante->inscricao_id,
            'course' => 'Enfermagem',
            'semester' => '3',
            'shift' => 2,
        ]);

        return $estudante;
    }

    public function test_csv_sai_com_status_situacao_e_dados_da_tabela(): void
    {
        $this->estudante(['status' => 'Lista de espera', 'situacao' => 'Inativo']);

        $resposta = $this->get('/api/exportar-estudantes/csv')->assertOk();
        $conteudo = file_get_contents($resposta->baseResponse->getFile()->getPathname());

        $this->assertStringStartsWith("\xEF\xBB\xBF", $conteudo, 'BOM para o Excel reconhecer UTF-8');
        $this->assertStringContainsString('"Nome";"Email";"CPF"', $conteudo);
        $this->assertStringContainsString('"Status";"Situação"', $conteudo);
        $this->assertStringContainsString('"Ana Conceição"', $conteudo);
        $this->assertStringContainsString('"123.456.789-00"', $conteudo);
        $this->assertStringContainsString('"(12) 98123-4567"', $conteudo);
        $this->assertStringContainsString('"FATEC Caraguatatuba";"Enfermagem";"3º semestre";"Noturno";"Linha Centro";"Lista de espera";"Inativo"', $conteudo);
        $this->assertStringContainsString('estudantes-', $resposta->headers->get('content-disposition'));
    }

    public function test_estudante_sem_linha_aparece_como_sem_linha(): void
    {
        $this->estudante(['linha_id' => null]);

        $resposta = $this->get('/api/exportar-estudantes/csv')->assertOk();
        $conteudo = file_get_contents($resposta->baseResponse->getFile()->getPathname());

        $this->assertStringContainsString('"Sem linha";"Aprovado";"Ativo"', $conteudo);
    }

    public function test_pdf_e_gerado(): void
    {
        $this->estudante();

        $resposta = $this->get('/api/exportar-estudantes/pdf')->assertOk();

        $this->assertSame('application/pdf', $resposta->headers->get('content-type'));
        $this->assertStringStartsWith('%PDF', $resposta->getContent());
    }

    public function test_xlsx_e_gerado(): void
    {
        $this->estudante();

        $this->get('/api/exportar-estudantes/xlsx')->assertOk()->assertDownload();
    }

    public function test_formato_invalido_e_recusado(): void
    {
        $this->getJson('/api/exportar-estudantes/doc')->assertStatus(400);
    }
}
