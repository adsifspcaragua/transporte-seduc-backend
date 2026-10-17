<?php

namespace App\Http\Controllers\Api\Viagem;

use App\Http\Controllers\Controller;
use App\Http\Requests\Viagem\DiaSemOperacaoRequest;
use App\Http\Requests\Viagem\HorarioViagemRequest;
use App\Services\Viagem\ProgramacaoService;
use Illuminate\Http\Request;

/**
 * @group Presenca - Programacao
 *
 * Horarios de ida e volta de cada linha e dias sem operacao (feriados,
 * recessos, paralisacoes). Sao a base das viagens datadas.
 *
 * @authenticated
 */
class ProgramacaoController extends Controller
{
    public function __construct(private readonly ProgramacaoService $service) {}

    /**
     * Listar horarios.
     */
    public function horarios(Request $request)
    {
        $filtros = $request->validate([
            'linha_id' => 'sometimes|integer',
            'ativo' => 'sometimes|boolean',
        ]);

        return $this->service->horarios($filtros);
    }

    /**
     * Exibir horario.
     *
     * @urlParam horario integer required ID do horario. Example: 1
     */
    public function horario(string $horario)
    {
        return $this->service->horario($horario);
    }

    /**
     * Cadastrar horario.
     */
    public function criarHorario(HorarioViagemRequest $request)
    {
        return $this->service->criarHorario($request->user(), $request->validated());
    }

    /**
     * Atualizar horario.
     *
     * Viagens futuras sem presenca e sem ajuste manual sao refeitas com a regra nova.
     *
     * @urlParam horario integer required ID do horario. Example: 1
     */
    public function atualizarHorario(HorarioViagemRequest $request, string $horario)
    {
        return $this->service->atualizarHorario($request->user(), $horario, $request->validated());
    }

    /**
     * Remover horario.
     *
     * @urlParam horario integer required ID do horario. Example: 1
     */
    public function removerHorario(Request $request, string $horario)
    {
        return $this->service->removerHorario($request->user(), $horario);
    }

    /**
     * Listar dias sem operacao.
     */
    public function diasSemOperacao(Request $request)
    {
        $filtros = $request->validate([
            'de' => 'sometimes|date_format:Y-m-d',
            'ate' => 'sometimes|date_format:Y-m-d',
        ]);

        return $this->service->diasSemOperacao($filtros);
    }

    /**
     * Registrar dia sem operacao.
     *
     * Cancela as viagens ja geradas do dia (da linha, ou de todas).
     */
    public function criarDiaSemOperacao(DiaSemOperacaoRequest $request)
    {
        return $this->service->criarDiaSemOperacao($request->user(), $request->validated());
    }

    /**
     * Remover dia sem operacao.
     *
     * Nao reativa as viagens canceladas: reative-as em "Ajustar viagem".
     *
     * @urlParam dia integer required ID do registro. Example: 1
     */
    public function removerDiaSemOperacao(Request $request, string $dia)
    {
        return $this->service->removerDiaSemOperacao($request->user(), $dia);
    }
}
