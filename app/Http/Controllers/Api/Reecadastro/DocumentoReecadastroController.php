<?php

namespace App\Http\Controllers\Api\Reecadastro;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reecadastro\Documento\StoreDocumentoRequest;
use App\Http\Requests\Reecadastro\Documento\UpdateDocumentoRequest;
use App\Services\Reecadastro\DocumentoReecadastroService;

class DocumentoReecadastroController extends Controller
{
    public function __construct(private readonly DocumentoReecadastroService $documentoReecadastroService) {}

    public function index()
    {
        return $this->documentoReecadastroService->index();
    }

    public function store(StoreDocumentoRequest $request)
    {
        return $this->documentoReecadastroService->store($request->validated());
    }

    public function show(string $id)
    {
        return $this->documentoReecadastroService->show($id);
    }

    public function update(UpdateDocumentoRequest $request, string $id)
    {
        return $this->documentoReecadastroService->update($request->validated(), $id);
    }

    public function destroy(string $id)
    {
        return $this->documentoReecadastroService->destroy($id);
    }
}
