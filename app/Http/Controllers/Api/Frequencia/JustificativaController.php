<?php

namespace App\Http\Controllers\Api\Frequencia;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frequencia\AnalisarJustificativaRequest;
use App\Http\Requests\Frequencia\CriarJustificativaRequest;
use App\Http\Requests\Frequencia\ListarJustificativasRequest;
use App\Services\Frequencia\JustificativaService;
use Illuminate\Http\Request;

/**
 * @group Frequencia
 *
 * Justificativas de falta, analisadas pela responsavel. Aprovar retira a falta;
 * rejeitar faz a falta voltar a contar para a regra do beneficio.
 *
 * @authenticated
 */
class JustificativaController extends Controller
{
    public function __construct(private readonly JustificativaService $service) {}

    /**
     * Listar justificativas.
     *
     * As que esperam analise vem primeiro, da mais antiga para a mais nova.
     * `em_analise` traz o total pendente, para o contador da aba.
     */
    public function index(ListarJustificativasRequest $request)
    {
        return $this->service->index($request->user(), $request->validated());
    }

    /**
     * Justificar falta.
     *
     * Envia para analise a justificativa de uma falta ja registrada, mesmo com a
     * chamada fechada.
     */
    public function store(CriarJustificativaRequest $request)
    {
        return $this->service->criar($request->user(), $request->validated());
    }

    /**
     * Exibir justificativa.
     *
     * @urlParam justificativa integer required ID da justificativa. Example: 1
     */
    public function show(Request $request, string $justificativa)
    {
        return $this->service->show($request->user(), $justificativa);
    }

    /**
     * Analisar justificativa.
     *
     * Aprovada retira a falta. Rejeitada torna a justificativa invalida: a
     * marcacao volta a ser Falta e a regra do beneficio e reaplicada.
     *
     * @urlParam justificativa integer required ID da justificativa. Example: 1
     */
    public function analise(AnalisarJustificativaRequest $request, string $justificativa)
    {
        return $this->service->analisar($request->user(), $justificativa, $request->validated());
    }
}
