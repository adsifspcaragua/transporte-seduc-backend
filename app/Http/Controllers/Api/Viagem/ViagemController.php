<?php

namespace App\Http\Controllers\Api\Viagem;

use App\Http\Controllers\Controller;
use App\Http\Requests\Viagem\AjustarViagemRequest;
use App\Http\Requests\Viagem\GerarViagensRequest;
use App\Models\HorarioViagem;
use App\Models\Viagem;
use App\Services\Viagem\ViagemService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * @group Presenca - Viagens
 *
 * Ocorrencias datadas de cada horario (ida e volta separadas). Sao geradas
 * automaticamente; a gestao pode gerar antes e ajustar janela, van e status.
 *
 * @authenticated
 */
class ViagemController extends Controller
{
    public function __construct(private readonly ViagemService $service) {}

    /**
     * Listar viagens.
     */
    public function index(Request $request)
    {
        $filtros = $request->validate([
            'data' => 'sometimes|date_format:Y-m-d',
            'de' => 'sometimes|date_format:Y-m-d',
            'ate' => 'sometimes|date_format:Y-m-d',
            'linha_id' => 'sometimes|integer',
            'veiculo_id' => 'sometimes|integer',
            'status' => ['sometimes', Rule::in(Viagem::STATUS)],
            'sentido' => ['sometimes', Rule::in(HorarioViagem::SENTIDOS)],
            'per_page' => 'sometimes|integer|in:10,15,20,30,50',
        ]);

        return $this->service->index($filtros);
    }

    /**
     * Exibir viagem.
     *
     * @urlParam viagem integer required ID da viagem. Example: 1
     */
    public function show(string $viagem)
    {
        return $this->service->show($viagem);
    }

    /**
     * Gerar viagens.
     *
     * Idempotente. Devolve as criadas e os conflitos de van (mesma van em
     * janelas sobrepostas), que impedem o QR de apontar uma viagem so.
     */
    public function gerar(GerarViagensRequest $request)
    {
        return $this->service->gerar($request->validated());
    }

    /**
     * Ajustar viagem.
     *
     * Atraso (janela), troca de van so desta viagem ou cancelamento/reativacao.
     * Motivo obrigatorio; fica na auditoria.
     *
     * @urlParam viagem integer required ID da viagem. Example: 1
     */
    public function update(AjustarViagemRequest $request, string $viagem)
    {
        return $this->service->update($request->user(), $viagem, $request->validated());
    }
}
