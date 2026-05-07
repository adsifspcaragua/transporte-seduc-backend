<?php

namespace App\Http\Controllers\Api\Estudante;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inscricao\StoreInscricaoRequest;
use App\Http\Requests\Inscricao\UpdateInscricaoRequest;
use App\Services\Inscricao\InscricaoService;

class InscricaoController extends Controller
{
    public function __construct(private readonly InscricaoService $inscricaoService) {}

    public function index()
    {
        return $this->inscricaoService->index();
    }

    public function store(StoreInscricaoRequest $request)
    {
        return $this->inscricaoService->store($request->validated());
    }

    public function show(string $id)
    {
        return $this->inscricaoService->show($id);
    }

    public function update(UpdateInscricaoRequest $request, $id)
    {
        return $this->inscricaoService->update($request->validated(), $id);
    }

    public function destroy(string $id)
    {
        return $this->inscricaoService->destroy($id);
    }

    public function recadastro()
    {
        return $this->inscricaoService->recadastro();
    }
}
