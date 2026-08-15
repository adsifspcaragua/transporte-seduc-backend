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
 *   N4  if (! accepted_terms || ! accepted_terms_2) -> retorna false
 *   N5  if (! $instituicao)                       -> retorna false
 *   N6  loop campos da instituicao: campo == null -> retorna false
 *   N7  documento obrigatório ausente             -> retorna false
 *   N8  return true                               -> retorna true
 *
 * Cada teste abaixo foi projetado para exercitar um arco distinto
 * (criterio "todos os arcos").
 *
 * A lista de espera exige os documentos definidos no modelo.
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

    /** N4: dados completos, mas um dos termos nao foi aceito. */
    public function test_retorna_false_quando_termo_nao_foi_aceito(): void
    {
        $inscricao = Inscricao::factory()->create(['accepted_terms_2' => false]);
        InscricaoInstituicoes::factory()->create(['inscricao_id' => $inscricao->id]);

        $this->assertFalse($this->service->isComplete($inscricao));
    }

    /** N5: inscricao completa, mas sem vinculo de instituicao. */
    public function test_retorna_false_quando_nao_ha_instituicao_vinculada(): void
    {
        $inscricao = Inscricao::factory()->create();

        $this->assertFalse($this->service->isComplete($inscricao));
    }

    /** N6: um campo obrigatorio da instituicao esta nulo. */
    public function test_retorna_false_quando_campo_da_instituicao_esta_nulo(): void
    {
        $inscricao = Inscricao::factory()->create();
        InscricaoInstituicoes::factory()->create([
            'inscricao_id' => $inscricao->id,
            'course' => null,
        ]);

        $this->assertFalse($this->service->isComplete($inscricao));
    }

    /** N7: sem documentos, a inscricao continua incompleta. */
    public function test_retorna_false_quando_falta_documento_obrigatorio(): void
    {
        $inscricao = Inscricao::factory()->create();
        InscricaoInstituicoes::factory()->create(['inscricao_id' => $inscricao->id]);

        $this->assertFalse($this->service->isComplete($inscricao));
    }

    /** N8: dados, termos e documentos completos. */
    public function test_retorna_true_quando_tudo_esta_completo(): void
    {
        $inscricao = $this->inscricaoComInstituicaoCompleta();

        $this->assertTrue($this->service->isComplete($inscricao));
    }

    /** refreshStatus deve gravar 'Em analise' quando a inscricao esta completa. */
    public function test_refresh_status_marca_em_analise_quando_completa(): void
    {
        $inscricao = $this->inscricaoComInstituicaoCompleta();

        $this->assertSame('Em analise', $this->service->refreshStatus($inscricao)->status);
    }

    /** refreshStatus deve gravar 'Incompleto' quando a inscricao nao esta completa. */
    public function test_refresh_status_marca_incompleto_quando_nao_completa(): void
    {
        $inscricao = Inscricao::factory()->create(['status' => 'Em analise']);

        $atualizada = $this->service->refreshStatus($inscricao);

        $this->assertSame('Incompleto', $atualizada->status);
    }
}
