<?php

namespace App\Http\Controllers\Api\Reecadastro;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reecadastro\Solicitacao\StoreSolicitacaoRequest;
use App\Http\Requests\Reecadastro\Solicitacao\UpdateSolicitacaoRequest;
use App\Services\Reecadastro\SolicitacaoReecadastroService;

class SolicitacaoReecadastroController extends Controller
{
    public function __construct(private readonly SolicitacaoReecadastroService $solicitacaoReecadastroService) {}

    public function index()
    {
        return $this->solicitacaoReecadastroService->index();
    }

    public function store(StoreSolicitacaoRequest $request)
    {
        return $this->solicitacaoReecadastroService->store($request->validated());
    }

    public function show(string $id)
    {
        return $this->solicitacaoReecadastroService->show($id);
    }

    public function update(UpdateSolicitacaoRequest $request, string $id)
    {
        return $this->solicitacaoReecadastroService->update($request->validated(), $id);
    }

    public function destroy(string $id)
    {
        return $this->solicitacaoReecadastroService->destroy($id);
    }
}
