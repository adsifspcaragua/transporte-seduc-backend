<?php

namespace App\Http\Controllers\Api\Estudante\Documento;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inscricao\Documento\StoreDocumentoRequest;
use App\Models\Inscricao;
use App\Models\InscricaoDocumento;
use App\Services\Inscricao\Documento\InscricaoDocumentoService;
use Illuminate\Http\Request;

class InscricaoDocumentoController extends Controller
{
    public function __construct(private readonly InscricaoDocumentoService $service) {}

    public function index(Inscricao $inscricao)
    {
        return $this->service->index($inscricao);
    }

    public function store(StoreDocumentoRequest $request, Inscricao $inscricao)
    {
        return $this->service->store($request, $inscricao);
    }

    public function destroy(Inscricao $inscricao, InscricaoDocumento $documento)
    {
        return $this->service->destroy($inscricao, $documento);
    }

    public function download(Request $request, Inscricao $inscricao, InscricaoDocumento $documento)
    {
        return $this->service->download($inscricao, $documento, $request->boolean('inline'));
    }
}
