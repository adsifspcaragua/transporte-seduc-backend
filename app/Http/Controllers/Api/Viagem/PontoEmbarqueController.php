<?php

namespace App\Http\Controllers\Api\Viagem;

use App\Http\Controllers\Controller;
use App\Http\Requests\Viagem\LinhaPontoRequest;
use App\Http\Requests\Viagem\PontoEmbarqueRequest;
use App\Models\HorarioViagem;
use App\Services\Viagem\PontoEmbarqueService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * @group Presenca - Geocercas
 *
 * Pontos fixos de embarque com raio proprio (Alternativa A do roadmap) e em
 * quais linhas e sentidos cada ponto vale. Sem ponto cadastrado para a linha
 * e o sentido, a presenca nao e aprovada automaticamente.
 *
 * @authenticated
 */
class PontoEmbarqueController extends Controller
{
    public function __construct(private readonly PontoEmbarqueService $service) {}

    /**
     * Listar pontos.
     */
    public function index()
    {
        return $this->service->index();
    }

    /**
     * Exibir ponto.
     *
     * @urlParam ponto integer required ID do ponto. Example: 1
     */
    public function show(string $ponto)
    {
        return $this->service->show($ponto);
    }

    /**
     * Cadastrar ponto.
     *
     * O raio e obrigatorio: nao ha padrao, depende de teste de campo.
     */
    public function store(PontoEmbarqueRequest $request)
    {
        return $this->service->store($request->user(), $request->validated());
    }

    /**
     * Atualizar ponto.
     *
     * @urlParam ponto integer required ID do ponto. Example: 1
     */
    public function update(PontoEmbarqueRequest $request, string $ponto)
    {
        return $this->service->update($request->user(), $ponto, $request->validated());
    }

    /**
     * Excluir ponto.
     *
     * So sem presencas registradas; caso contrario, inative.
     *
     * @urlParam ponto integer required ID do ponto. Example: 1
     */
    public function destroy(Request $request, string $ponto)
    {
        return $this->service->destroy($request->user(), $ponto);
    }

    /**
     * Listar pontos por linha.
     */
    public function vinculos(Request $request)
    {
        $filtros = $request->validate([
            'linha_id' => 'sometimes|integer',
            'sentido' => ['sometimes', Rule::in(HorarioViagem::SENTIDOS)],
        ]);

        return $this->service->vinculos($filtros);
    }

    /**
     * Vincular ponto a linha.
     */
    public function vincular(LinhaPontoRequest $request)
    {
        return $this->service->vincular($request->user(), $request->validated());
    }

    /**
     * Atualizar vinculo.
     *
     * @urlParam vinculo integer required ID do vinculo. Example: 1
     */
    public function atualizarVinculo(LinhaPontoRequest $request, string $vinculo)
    {
        return $this->service->atualizarVinculo($request->user(), $vinculo, $request->validated());
    }

    /**
     * Desvincular ponto da linha.
     *
     * @urlParam vinculo integer required ID do vinculo. Example: 1
     */
    public function desvincular(Request $request, string $vinculo)
    {
        return $this->service->desvincular($request->user(), $vinculo);
    }
}
