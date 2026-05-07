<?php

namespace App\Http\Controllers\Api\Estudante\Documento;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inscricao\Documento\StoreDocumentoRequest;
use App\Http\Requests\Inscricao\Documento\UpdateDocumentoRequest;
use App\Models\Inscricao;
use App\Models\InscricaoDocumento;
use App\Services\Inscricao\Documento\InscricaoDocumentoService;

class InscricaoDocumentoController extends Controller
{
    public function __construct(private readonly InscricaoDocumentoService $inscricaoDocumentoService) {}

    public function index(Inscricao $inscricao)
    {
        return $this->inscricaoDocumentoService->index($inscricao);
    }

    public function store(StoreDocumentoRequest $request, Inscricao $inscricao)
    {
        return $this->inscricaoDocumentoService->store($request, $inscricao);
    }

    public function show(Inscricao $inscricao, InscricaoDocumento $documento)
    {
        return $this->inscricaoDocumentoService->show($inscricao, $documento);
    }

    public function update(UpdateDocumentoRequest $request, Inscricao $inscricao, InscricaoDocumento $documento)
    {
        return $this->inscricaoDocumentoService->update($request, $inscricao, $documento);
    }

    public function destroy(Inscricao $inscricao, InscricaoDocumento $documento)
    {
        return $this->inscricaoDocumentoService->destroy($inscricao, $documento);
    }
}
