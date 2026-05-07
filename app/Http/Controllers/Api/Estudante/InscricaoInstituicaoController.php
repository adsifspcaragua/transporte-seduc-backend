<?php

namespace App\Http\Controllers\Api\Estudante;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inscricao\Instituicao\StoreInscricaoInstituicaoRequest;
use App\Http\Requests\Inscricao\Instituicao\UpdateInscricaoInstituicaoRequest;
use App\Services\Inscricao\Instituicao\InscricaoInstituicaoService;

class InscricaoInstituicaoController extends Controller
{
    public function __construct(private readonly InscricaoInstituicaoService $inscricaoInstituicaoService) {}

    public function index(string $inscricao_id)
    {
        return $this->inscricaoInstituicaoService->index($inscricao_id);
    }

    public function store(StoreInscricaoInstituicaoRequest $request, string $inscricao_id)
    {
        return $this->inscricaoInstituicaoService->store($request->validated());
    }

    public function show(string $inscricao_id, string $instituicao)
    {
        return $this->inscricaoInstituicaoService->show($inscricao_id, $instituicao);
    }

    public function update(UpdateInscricaoInstituicaoRequest $request, string $inscricao_id, string $instituicao)
    {
        return $this->inscricaoInstituicaoService->update($request->validated(), $inscricao_id, $instituicao);
    }

    public function destroy(string $inscricao_id, string $instituicao)
    {
        return $this->inscricaoInstituicaoService->destroy($inscricao_id, $instituicao);
    }
}
