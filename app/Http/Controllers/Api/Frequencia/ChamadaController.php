<?php

namespace App\Http\Controllers\Api\Frequencia;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frequencia\AbrirChamadaRequest;
use App\Http\Requests\Frequencia\ListarChamadasRequest;
use App\Http\Requests\Frequencia\RegistrarFrequenciaRequest;
use App\Services\Frequencia\ChamadaService;
use Illuminate\Http\Request;

/**
 * @group Frequencia
 *
 * Chamada diaria feita pelo motorista: uma folha por linha por dia, com cada
 * estudante marcado como Presente, Falta ou Justificada.
 *
 * O motorista so acessa as linhas que conduz; a secretaria acessa todas.
 *
 * @authenticated
 */
class ChamadaController extends Controller
{
    public function __construct(private readonly ChamadaService $service) {}

    /**
     * Listar linhas para chamada.
     *
     * Linhas em que o usuario pode fazer chamada, com a situacao da chamada de hoje.
     */
    public function linhas(Request $request)
    {
        return $this->service->linhas($request->user());
    }

    /**
     * Listar chamadas.
     *
     * Retorna as chamadas com os contadores de presenca, paginadas.
     */
    public function index(ListarChamadasRequest $request)
    {
        return $this->service->index($request->user(), $request->validated());
    }

    /**
     * Abrir chamada.
     *
     * Abre a folha do dia da linha com os estudantes esperados, todos pendentes.
     * Se a folha do dia ja existe, devolve a existente (status 200) para o
     * motorista continuar de onde parou.
     */
    public function store(AbrirChamadaRequest $request)
    {
        return $this->service->abrir($request->user(), $request->validated());
    }

    /**
     * Exibir chamada.
     *
     * Retorna a folha com todos os estudantes e suas marcacoes, em ordem alfabetica.
     *
     * @urlParam chamada integer required ID da chamada. Example: 1
     */
    public function show(Request $request, string $chamada)
    {
        return $this->service->show($request->user(), $chamada);
    }

    /**
     * Registrar presencas e faltas.
     *
     * Marca um ou varios estudantes da folha. Estudantes que nao estao na folha
     * sao devolvidos em `ignorados`.
     *
     * @urlParam chamada integer required ID da chamada. Example: 1
     */
    public function update(RegistrarFrequenciaRequest $request, string $chamada)
    {
        return $this->service->registrar($request->user(), $chamada, $request->validated()['frequencias']);
    }

    /**
     * Fechar chamada.
     *
     * Entrega a folha. So e possivel com todos os estudantes marcados.
     *
     * @urlParam chamada integer required ID da chamada. Example: 1
     */
    public function fechar(Request $request, string $chamada)
    {
        return $this->service->fechar($request->user(), $chamada);
    }

    /**
     * Reabrir chamada.
     *
     * Devolve a folha fechada para correcao.
     *
     * @urlParam chamada integer required ID da chamada. Example: 1
     */
    public function reabrir(Request $request, string $chamada)
    {
        return $this->service->reabrir($request->user(), $chamada);
    }

    /**
     * Remover chamada.
     *
     * Exclui a folha e todas as suas marcacoes.
     *
     * @urlParam chamada integer required ID da chamada. Example: 1
     */
    public function destroy(Request $request, string $chamada)
    {
        return $this->service->destroy($request->user(), $chamada);
    }
}
