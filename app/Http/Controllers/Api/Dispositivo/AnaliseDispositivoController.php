<?php

namespace App\Http\Controllers\Api\Dispositivo;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dispositivo\AnalisarSolicitacaoDispositivoRequest;
use App\Http\Requests\Dispositivo\MotivoRequest;
use App\Models\DispositivoEstudante;
use App\Models\SolicitacaoDispositivo;
use App\Services\Dispositivo\AnaliseDispositivoService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * @group Presenca - Dispositivos (gestao)
 *
 * Atendimento: confere a identidade do estudante, aprova ou rejeita a
 * vinculacao/troca e revoga credenciais. Tudo com responsavel e justificativa.
 *
 * @authenticated
 */
class AnaliseDispositivoController extends Controller
{
    public function __construct(private readonly AnaliseDispositivoService $service) {}

    /**
     * Listar solicitacoes.
     *
     * Pendentes primeiro, das mais antigas para as mais novas.
     */
    public function solicitacoes(Request $request)
    {
        $filtros = $request->validate([
            'status' => ['sometimes', Rule::in([
                SolicitacaoDispositivo::PENDENTE, SolicitacaoDispositivo::APROVADA, SolicitacaoDispositivo::REJEITADA,
                SolicitacaoDispositivo::CONCLUIDA, SolicitacaoDispositivo::CANCELADA, SolicitacaoDispositivo::EXPIRADA,
            ])],
            'tipo' => ['sometimes', Rule::in([SolicitacaoDispositivo::VINCULACAO, SolicitacaoDispositivo::TROCA])],
            'estudante_id' => 'sometimes|integer',
            'per_page' => 'sometimes|integer|in:10,15,20,30',
        ]);

        return $this->service->solicitacoes($filtros);
    }

    /**
     * Exibir solicitacao.
     *
     * @urlParam solicitacao integer required ID da solicitacao. Example: 1
     */
    public function solicitacao(string $solicitacao)
    {
        return $this->service->solicitacao($solicitacao);
    }

    /**
     * Aprovar solicitacao.
     *
     * Depois de conferir a identidade. A aprovacao vale por pouco tempo: o
     * estudante conclui a vinculacao no navegador ainda no atendimento.
     *
     * @urlParam solicitacao integer required ID da solicitacao. Example: 1
     */
    public function aprovar(AnalisarSolicitacaoDispositivoRequest $request, string $solicitacao)
    {
        return $this->service->aprovar($request->user(), $solicitacao, $request->validated()['parecer']);
    }

    /**
     * Rejeitar solicitacao.
     *
     * @urlParam solicitacao integer required ID da solicitacao. Example: 1
     */
    public function rejeitar(AnalisarSolicitacaoDispositivoRequest $request, string $solicitacao)
    {
        return $this->service->rejeitar($request->user(), $solicitacao, $request->validated()['parecer']);
    }

    /**
     * Listar dispositivos.
     */
    public function dispositivos(Request $request)
    {
        $filtros = $request->validate([
            'status' => ['sometimes', Rule::in([DispositivoEstudante::ATIVO, DispositivoEstudante::REVOGADO])],
            'estudante_id' => 'sometimes|integer',
            'per_page' => 'sometimes|integer|in:10,15,20,30',
        ]);

        return $this->service->dispositivos($filtros);
    }

    /**
     * Revogar dispositivo.
     *
     * Bloqueia novas presencas na hora.
     *
     * @urlParam dispositivo string required UUID do dispositivo. Example: 9d1c2f0e-8f5a-4c1e-9b7a-1a2b3c4d5e6f
     */
    public function revogar(MotivoRequest $request, string $dispositivo)
    {
        return $this->service->revogar($request->user(), $dispositivo, $request->validated()['motivo']);
    }
}
