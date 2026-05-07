<?php

namespace App\Http\Controllers\Api\Instituicao;

use App\Http\Controllers\Controller;
use App\Http\Requests\Instituicao\StoreInstituicaoRequest;
use App\Http\Requests\Instituicao\UpdateInstituicaoRequest;
use App\Services\Instituicao\InstituicaoService;

class InstituicaoController extends Controller
{
    public function __construct(private readonly InstituicaoService $instituicaoService) {}

    public function index()
    {
        return $this->instituicaoService->index();
    }

    public function store(StoreInstituicaoRequest $request)
    {
        return $this->instituicaoService->store($request->validated());
    }

    public function show(string $id)
    {
        return $this->instituicaoService->show($id);
    }

    public function update(UpdateInstituicaoRequest $request, string $id)
    {
        return $this->instituicaoService->update($request->validated(), $id);
    }

    public function destroy(string $id)
    {
        return $this->instituicaoService->destroy($id);
    }
}
