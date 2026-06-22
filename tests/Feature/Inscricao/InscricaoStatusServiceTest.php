<?php

namespace Tests\Feature\Inscricao;

use App\Models\Inscricao;
use App\Models\InscricaoDocumento;
use App\Models\InscricaoInstituicoes;
use App\Services\Inscricao\InscricaoStatusService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * TESTE ESTRUTURAL (caixa-branca).
 *
 * Alvo: InscricaoStatusService::isComplete(), que possui varios pontos de
 * decisao (nos) e ramos (arcos) no seu grafo de fluxo de controle:
 *
 *   N1  if (! $inscricao)                         -> retorna false
 *   N2  if (! $inscricaoCompleta)  (find falhou)  -> retorna false
 *   N3  loop campos da inscricao: campo == null   -> retorna false
 *   N4  if (! $instituicao)                        -> retorna false
 *   N5  loop campos da instituicao: campo == null -> retorna false
 *   N6  loop documentos: doc ausente / status     -> retorna false
 *   N7  return $todosValidos                       -> retorna true
 *
 * Cada teste abaixo foi projetado para exercitar um arco distinto
 * (criterio "todos os arcos").
 */
class InscricaoStatusServiceTest extends TestCase
{
    use RefreshDatabase;

    private InscricaoStatusService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new InscricaoStatusService;
    }

    /** Cria uma inscricao + vinculo de instituicao, ambos completos. */
    private function inscricaoComInstituicaoCompleta(): Inscricao
    {
        $inscricao = Inscricao::factory()->create();
        InscricaoInstituicoes::factory()->create(['inscricao_id' => $inscricao->id]);

        return $inscricao;
    }

    /** N1: entrada nula. */
    public function test_retorna_false_quando_inscricao_e_nula(): void
    {
        $this->assertFalse($this->service->isComplete(null));
    }

    /** N2: inscricao nao persistida (find devolve null). */
    public function test_retorna_false_quando_inscricao_nao_existe_no_banco(): void
    {
        $fantasma = new Inscricao;
        $fantasma->id = 999999;

        $this->assertFalse($this->service->isComplete($fantasma));
    }

    /** N3: um campo obrigatorio da inscricao esta nulo. */
    public function test_retorna_false_quando_campo_da_inscricao_esta_nulo(): void
    {
        $inscricao = Inscricao::factory()->create(['email' => null]);
        InscricaoInstituicoes::factory()->create(['inscricao_id' => $inscricao->id]);

        $this->assertFalse($this->service->isComplete($inscricao));
    }

    /** N4: inscricao completa, mas sem vinculo de instituicao. */
    public function test_retorna_false_quando_nao_ha_instituicao_vinculada(): void
    {
        $inscricao = Inscricao::factory()->create();

        $this->assertFalse($this->service->isComplete($inscricao));
    }

    /** N5: um campo obrigatorio da instituicao esta nulo. */
    public function test_retorna_false_quando_campo_da_instituicao_esta_nulo(): void
    {
        $inscricao = Inscricao::factory()->create();
        InscricaoInstituicoes::factory()->create([
            'inscricao_id' => $inscricao->id,
            'course' => null,
        ]);

        $this->assertFalse($this->service->isComplete($inscricao));
    }

    /** N6 (arco "documento ausente"): nenhum documento enviado. */
    public function test_retorna_false_quando_documento_obrigatorio_esta_ausente(): void
    {
        $inscricao = $this->inscricaoComInstituicaoCompleta();

        $this->assertFalse($this->service->isComplete($inscricao));
    }

    /** N6 (arco "status invalido"): documento existe mas com status != 'Em analise'. */
    public function test_retorna_false_quando_documento_tem_status_invalido(): void
    {
        $inscricao = $this->inscricaoComInstituicaoCompleta();
        InscricaoDocumento::factory()->create([
            'inscricao_id' => $inscricao->id,
            'name' => 'foto',
            'status' => 'Aprovado',
        ]);

        $this->assertFalse($this->service->isComplete($inscricao));
    }

    /**
     * N7 (caminho "tudo valido" -> true).
     *
     * Inscricao completa + instituicao completa + os 7 documentos
     * obrigatorios (foto, identidade, residencia, historico, matricula,
     * cronograma, declaracao), todos com status 'Em analise'.
     *
     * NOTA DE MODELAGEM: a migration de inscricao_documentos tentou declarar
     * inscricao_id como UNIQUE (limitar a 1 documento por inscricao), mas o
     * ->unique() encadeado apos a foreign key nao gera indice algum no schema
     * real. Por isso multiplos documentos sao aceitos e este ramo e
     * alcancavel. Caso a unicidade venha a ser corrigida, este caminho se
     * tornaria um "requisito nao executavel".
     */
    public function test_retorna_true_quando_tudo_esta_completo(): void
    {
        $inscricao = $this->inscricaoComInstituicaoCompleta();

        $obrigatorios = ['foto', 'identidade', 'residencia', 'historico', 'matricula', 'cronograma', 'declaracao'];

        foreach ($obrigatorios as $nome) {
            InscricaoDocumento::factory()->create([
                'inscricao_id' => $inscricao->id,
                'name' => $nome,
                'status' => 'Em analise',
            ]);
        }

        $this->assertTrue($this->service->isComplete($inscricao));
    }

    /** refreshStatus deve gravar 'Em analise' quando a inscricao esta completa. */
    public function test_refresh_status_marca_em_analise_quando_completa(): void
    {
        $inscricao = $this->inscricaoComInstituicaoCompleta();

        foreach (['foto', 'identidade', 'residencia', 'historico', 'matricula', 'cronograma', 'declaracao'] as $nome) {
            InscricaoDocumento::factory()->create([
                'inscricao_id' => $inscricao->id,
                'name' => $nome,
                'status' => 'Em analise',
            ]);
        }

        $this->assertSame('Em analise', $this->service->refreshStatus($inscricao)->status);
    }

    /** refreshStatus deve gravar 'Incompleto' quando a inscricao nao esta completa. */
    public function test_refresh_status_marca_incompleto_quando_nao_completa(): void
    {
        $inscricao = Inscricao::factory()->create(['status' => 'Em analise']);

        $atualizada = $this->service->refreshStatus($inscricao);

        $this->assertSame('Incompleto', $atualizada->status);
    }

    /** acceptInscricao e refuseInscricao gravam os respectivos status. */
    public function test_accept_e_refuse_inscricao_gravam_status(): void
    {
        $inscricao = Inscricao::factory()->create();

        $this->assertSame('Aprovada', $this->service->acceptInscricao($inscricao)->status);
        $this->assertSame('Rejeitada', $this->service->refuseInscricao($inscricao)->status);
    }
}
