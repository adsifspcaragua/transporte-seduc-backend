<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Linha\StoreLinhaRequest;
use App\Http\Requests\Linha\UpdateLinhaRequest;
use App\Services\Linha\LinhaService;

class LinhaController extends Controller
{
    public function __construct(private readonly LinhaService $linhaService) {}

    public function index()
    {
        return $this->linhaService->index();
    }

    public function store(StoreLinhaRequest $request)
    {
        return $this->linhaService->store($request->validated());
    }

    public function show(string $id)
    {
        return $this->linhaService->show($id);
    }

    public function update(UpdateLinhaRequest $request, string $id)
    {
        return $this->linhaService->update($request->validated(), $id);
    }

    public function destroy(string $id)
    {
        return $this->linhaService->destroy($id);
    }
}
