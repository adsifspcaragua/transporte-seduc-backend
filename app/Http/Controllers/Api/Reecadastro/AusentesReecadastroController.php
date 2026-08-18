<?php

namespace App\Http\Controllers\Api\Reecadastro;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reecadastro\Periodo\InativarAusentesRequest;
use App\Services\Reecadastro\AusentesReecadastroService;

/**
 * @group Reecadastro
 *
 * Estudantes ativos que nao concluiram o recadastro do periodo.
 *
 * @authenticated
 */
class AusentesReecadastroController extends Controller
{
    public function __construct(private readonly AusentesReecadastroService $service) {}

    /**
     * Listar estudantes sem recadastro.
     *
     * @urlParam periodo integer required ID do periodo. Example: 1
     */
    public function index(string $periodo)
    {
        return $this->service->index($periodo);
    }

    /**
     * Inativar estudantes sem recadastro.
     *
     * Inativa apenas os estudantes indicados, e apenas os que de fato estao sem
     * recadastro no periodo.
     *
     * @urlParam periodo integer required ID do periodo. Example: 1
     */
    public function inativar(InativarAusentesRequest $request, string $periodo)
    {
        return $this->service->inativar($periodo, $request->validated()['estudantes']);
    }
}
