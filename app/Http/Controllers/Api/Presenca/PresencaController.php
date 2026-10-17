<?php

namespace App\Http\Controllers\Api\Presenca;

use App\Enums\CodigoPresenca;
use App\Http\Controllers\Controller;
use App\Http\Requests\Presenca\AnalisarRevisaoRequest;
use App\Http\Requests\Presenca\PresencaManualRequest;
use App\Models\Presenca;
use App\Models\TentativaPresenca;
use App\Services\Presenca\PresencaAdminService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * @group Presenca - Gestao
 *
 * Presencas registradas, tentativas negadas, pedidos de revisao, excecoes
 * administrativas e indicadores para calibrar raio, precisao e horarios.
 *
 * @authenticated
 */
class PresencaController extends Controller
{
    public function __construct(private readonly PresencaAdminService $service) {}

    /**
     * Listar presencas.
     */
    public function index(Request $request)
    {
        $filtros = $request->validate([
            'estudante_id' => 'sometimes|integer',
            'viagem_id' => 'sometimes|integer',
            'linha_id' => 'sometimes|integer',
            'metodo' => ['sometimes', Rule::in([Presenca::DISPOSITIVO, Presenca::MANUAL])],
            'de' => 'sometimes|date_format:Y-m-d',
            'ate' => 'sometimes|date_format:Y-m-d',
            'per_page' => 'sometimes|integer|in:10,15,20,30',
        ]);

        return $this->service->index($filtros);
    }

    /**
     * Registrar presenca manual.
     *
     * Excecao administrativa (celular perdido, GPS indisponivel...), com
     * justificativa obrigatoria e registro na auditoria.
     */
    public function manual(PresencaManualRequest $request)
    {
        return $this->service->registrarManual($request->user(), $request->validated());
    }

    /**
     * Listar tentativas.
     *
     * Pedidos de revisao pendentes primeiro.
     */
    public function tentativas(Request $request)
    {
        $filtros = $request->validate([
            'resultado' => ['sometimes', Rule::in([TentativaPresenca::REGISTRADA, TentativaPresenca::NEGADA])],
            'codigo' => ['sometimes', Rule::enum(CodigoPresenca::class)],
            'revisao_status' => ['sometimes', Rule::in([TentativaPresenca::REVISAO_PENDENTE, TentativaPresenca::REVISAO_APROVADA, TentativaPresenca::REVISAO_REJEITADA])],
            'estudante_id' => 'sometimes|integer',
            'linha_id' => 'sometimes|integer',
            'de' => 'sometimes|date_format:Y-m-d',
            'ate' => 'sometimes|date_format:Y-m-d',
            'per_page' => 'sometimes|integer|in:10,15,20,30',
        ]);

        return $this->service->tentativas($filtros);
    }

    /**
     * Analisar pedido de revisao.
     *
     * Aprovada registra a presenca como manual, com o parecer como justificativa.
     *
     * @urlParam tentativa integer required ID da tentativa. Example: 1
     */
    public function analisarRevisao(AnalisarRevisaoRequest $request, string $tentativa)
    {
        $data = $request->validated();

        return $this->service->analisarRevisao($request->user(), $tentativa, $data['decisao'], $data['parecer']);
    }

    /**
     * Indicadores.
     *
     * Presencas por linha e dia, negativas por motivo, qualidade do GPS e
     * revisoes. Padrao: ultimos 30 dias.
     */
    public function indicadores(Request $request)
    {
        $filtros = $request->validate([
            'de' => 'sometimes|date_format:Y-m-d',
            'ate' => 'sometimes|date_format:Y-m-d|after_or_equal:de',
            'linha_id' => 'sometimes|integer',
        ]);

        return $this->service->indicadores($filtros);
    }
}
