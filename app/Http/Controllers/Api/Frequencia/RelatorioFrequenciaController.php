<?php

namespace App\Http\Controllers\Api\Frequencia;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frequencia\RelatorioFrequenciaRequest;
use App\Services\Frequencia\RelatorioFrequenciaService;

/**
 * @group Frequencia
 *
 * Relatorios de frequencia consolidados a partir das chamadas.
 *
 * @authenticated
 */
class RelatorioFrequenciaController extends Controller
{
    public function __construct(private readonly RelatorioFrequenciaService $service) {}

    /**
     * Relatorio de frequencia.
     *
     * Um resumo por estudante no periodo: presencas, faltas, faltas justificadas,
     * percentual de presenca e faltas consecutivas. Quem mais falta vem primeiro.
     */
    public function index(RelatorioFrequenciaRequest $request)
    {
        return $this->service->geral($request->user(), $request->validated());
    }

    /**
     * Frequencia do estudante.
     *
     * Resumo do estudante no periodo e o historico dia a dia.
     *
     * @urlParam estudante integer required ID do estudante. Example: 1
     */
    public function estudante(RelatorioFrequenciaRequest $request, string $estudante)
    {
        return $this->service->estudante($request->user(), $estudante, $request->validated());
    }
}
