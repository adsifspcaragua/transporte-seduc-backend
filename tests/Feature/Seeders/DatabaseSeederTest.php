<?php

namespace Tests\Feature\Seeders;

use App\Models\Estudante;
use App\Models\HorarioViagem;
use App\Models\Inscricao;
use App\Models\InscricaoInstituicoes;
use App\Models\Linha;
use App\Models\SolicitacaoReecadastro;
use App\Models\User;
use App\Models\Veiculo;
use App\Services\Inscricao\InscricaoStatusService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake();
        $this->seed(DatabaseSeeder::class);
    }

    private function cpfValido(string $cpf): bool
    {
        if (! preg_match('/^\d{11}$/', $cpf) || preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }

        foreach ([9, 10] as $tamanho) {
            $soma = 0;

            for ($i = 0; $i < $tamanho; $i++) {
                $soma += (int) $cpf[$i] * ($tamanho + 1 - $i);
            }

            if ((int) $cpf[$tamanho] !== ($soma * 10) % 11 % 10) {
                return false;
            }
        }

        return true;
    }

    public function test_quem_esta_na_lista_de_espera_nao_e_estudante(): void
    {
        $naLista = Inscricao::whereIn('status', ['Incompleto', 'Em analise', 'Em revisao'])->pluck('id');

        $this->assertNotEmpty($naLista);
        $this->assertSame(0, Estudante::whereIn('inscricao_id', $naLista)->count());
    }

    public function test_estudantes_vem_de_inscricoes_analisadas(): void
    {
        $this->assertSame(
            0,
            Estudante::whereHas('inscricao', fn ($q) => $q->whereNotIn('status', ['Aprovado', 'Rejeitado']))->count(),
        );
        $this->assertSame(0, Estudante::where('status', 'Aprovado')->whereHas('inscricao', fn ($q) => $q->where('status', '!=', 'Aprovado'))->count());
        $this->assertSame(0, Estudante::where('status', 'Recusado')->where('situacao', 'Ativo')->count());
    }

    public function test_todos_os_cpfs_sao_validos(): void
    {
        $cpfs = Inscricao::pluck('cpf')->merge(Estudante::pluck('cpf'));

        $this->assertGreaterThanOrEqual(40, Inscricao::count());

        foreach ($cpfs as $cpf) {
            $this->assertTrue($this->cpfValido($cpf), "CPF inválido na seeder: {$cpf}");
        }
    }

    public function test_cobre_todos_os_status(): void
    {
        foreach (['Incompleto', 'Em analise', 'Em revisao', 'Aprovado', 'Rejeitado'] as $status) {
            $this->assertTrue(Inscricao::where('status', $status)->exists(), "Sem inscrição {$status}");
        }

        foreach ([['Aprovado', 'Ativo'], ['Aprovado', 'Inativo'], ['Recusado', 'Inativo']] as [$status, $situacao]) {
            $this->assertTrue(
                Estudante::where('status', $status)->where('situacao', $situacao)->exists(),
                "Sem estudante {$status}/{$situacao}",
            );
        }

        foreach (['Pendente', 'Em analise', 'Pendencia', 'Aprovado', 'Rejeitado'] as $status) {
            $this->assertTrue(SolicitacaoReecadastro::where('status', $status)->exists(), "Sem recadastro {$status}");
        }

        $this->assertTrue(Estudante::where('situacao', 'Ativo')->whereNull('linha_id')->exists(), 'Sem ativo sem linha');
    }

    public function test_inscricoes_em_analise_estao_completas_para_aprovar(): void
    {
        $status = app(InscricaoStatusService::class);

        foreach (Inscricao::where('status', 'Em analise')->get() as $inscricao) {
            $this->assertTrue($status->isComplete($inscricao), "{$inscricao->name} em análise mas incompleta");
        }

        foreach (Inscricao::where('status', 'Em revisao')->get() as $inscricao) {
            $this->assertNotNull($inscricao->observation);
            $this->assertNotNull($inscricao->campos_pendentes);
        }
    }

    public function test_recadastro_rejeitado_deixa_o_estudante_inativo(): void
    {
        $this->assertSame(
            0,
            SolicitacaoReecadastro::where('status', 'Rejeitado')
                ->whereHas('estudante', fn ($q) => $q->where('situacao', 'Ativo'))
                ->count(),
        );
    }

    public function test_cidade_de_destino_e_sempre_sjc(): void
    {
        $this->assertSame(0, InscricaoInstituicoes::where('city_destination', '!=', InscricaoInstituicoes::CIDADE_DESTINO)->count());
    }

    public function test_documentos_existem_no_disco(): void
    {
        $inscricao = Inscricao::where('status', 'Em analise')->with('inscricao_documentos')->first();

        foreach ($inscricao->inscricao_documentos as $documento) {
            Storage::assertExists($documento->file_path);
        }
    }

    public function test_rodar_de_novo_nao_duplica(): void
    {
        $antes = [Inscricao::count(), Estudante::count(), SolicitacaoReecadastro::count(), Veiculo::count(), HorarioViagem::count(), Veiculo::pluck('qr_token_hash', 'id')->all()];

        $this->seed(DatabaseSeeder::class);

        $this->assertSame($antes, [Inscricao::count(), Estudante::count(), SolicitacaoReecadastro::count(), Veiculo::count(), HorarioViagem::count(), Veiculo::pluck('qr_token_hash', 'id')->all()]);
    }

    public function test_cenario_da_presenca_por_qr(): void
    {
        $this->assertSame(Linha::count(), Veiculo::whereNotNull('qr_token_hash')->count());
        $this->assertSame(Linha::count() * 2, HorarioViagem::count());

        $conta = User::where('email', 'estudante@example.com')->sole();
        $this->assertSame(['estudante'], $conta->roleTitles());

        $estudante = Estudante::where('user_id', $conta->id)->sole();
        $this->assertSame('Ativo', $estudante->situacao);
        $this->assertSame('Linha Centro', $estudante->linha->name);
    }
}
