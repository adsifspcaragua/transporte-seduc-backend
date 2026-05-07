<?php

namespace App\Http\Controllers\Api\Estudante;

use App\Http\Controllers\Controller;
use App\Http\Requests\Estudante\StoreEstudanteRequest;
use App\Http\Requests\Estudante\UpdateEstudanteRequest;
use App\Services\Estudante\EstudanteService;

class EstudanteController extends Controller
{
    public function __construct(private readonly EstudanteService $estudanteService) {}

    public function index()
    {
        return $this->estudanteService->index();
    }

    public function store(StoreEstudanteRequest $request)
    {
        return $this->estudanteService->store($request->validated());
    }

    public function show(string $id)
    {
        return $this->estudanteService->show($id);
    }

    public function update(UpdateEstudanteRequest $request, string $id)
    {
        return $this->estudanteService->update($request->validated(), $id);
    }

    public function destroy(string $id)
    {
        return $this->estudanteService->destroy($id);
    }

    public function countEstudantes()
    {
        return $this->estudanteService->countEstudantes();
    }

    public function estudantesAtivos()
    {
        return $this->estudanteService->estudantesAtivos();
    }
}
