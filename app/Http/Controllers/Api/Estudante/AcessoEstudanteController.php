<?php

namespace App\Http\Controllers\Api\Estudante;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reecadastro\Publico\ConsultaCpfRequest;
use App\Services\Estudante\AcessoEstudanteService;

class AcessoEstudanteController extends Controller
{
    public function __construct(private readonly AcessoEstudanteService $service) {}

    public function __invoke(ConsultaCpfRequest $request)
    {
        return $this->service->acessar($request->validated()['cpf']);
    }
}
