<?php

namespace App\Http\Controllers\Api\Viagem;

use App\Http\Controllers\Controller;
use App\Http\Requests\Viagem\AutorizacaoLinhaRequest;
use App\Services\Viagem\AutorizacaoLinhaService;
use Illuminate\Http\Request;

/**
 * @group Presenca - Programacao
 *
 * @authenticated
 */
class AutorizacaoLinhaController extends Controller
{
    public function __construct(private readonly AutorizacaoLinhaService $service) {}

    /**
     * Listar autorizacoes temporarias de linha.
     *
     * A linha do cadastro do estudante vale sempre; estas somam outras linhas
     * por um periodo (remanejamento).
     */
    public function index(Request $request)
    {
        $filtros = $request->validate([
            'estudante_id' => 'sometimes|integer',
            'linha_id' => 'sometimes|integer',
            'data' => 'sometimes|date_format:Y-m-d',
        ]);

        return $this->service->index($filtros);
    }

    /**
     * Autorizar linha temporariamente.
     */
    public function store(AutorizacaoLinhaRequest $request)
    {
        return $this->service->store($request->user(), $request->validated());
    }

    /**
     * Remover autorizacao.
     *
     * @urlParam autorizacao integer required ID da autorizacao. Example: 1
     */
    public function destroy(Request $request, string $autorizacao)
    {
        return $this->service->destroy($request->user(), $autorizacao);
    }
}
