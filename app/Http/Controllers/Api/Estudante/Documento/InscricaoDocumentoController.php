<?php

namespace App\Http\Controllers\Api\Estudante\Documento;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inscricao\Documento\StoreDocumentoRequest;
use App\Http\Requests\Inscricao\Documento\UpdateDocumentoRequest;
use App\Models\Inscricao;
use App\Models\InscricaoDocumento;
use App\Services\Inscricao\Documento\InscricaoDocumentoService;

/**
 * @group Documentos da inscricao
 *
 * Rotas para envio, consulta, atualizacao e remocao dos documentos da inscricao.
 *
 * @authenticated
 */
class InscricaoDocumentoController extends Controller
{
    public function __construct(private readonly InscricaoDocumentoService $inscricaoDocumentoService) {}

    /**
     * Listar documentos da inscricao.
     *
     * Retorna os documentos enviados para uma inscricao.
     *
     * @urlParam inscricao integer required ID da inscricao. Example: 1
     */
    public function index(Inscricao $inscricao)
    {
        return $this->inscricaoDocumentoService->index($inscricao);
    }

    /**
     * Enviar documento da inscricao.
     *
     * Envia um documento para a inscricao e recalcula o status.
     *
     * @urlParam inscricao integer required ID da inscricao. Example: 1
     */
    public function store(StoreDocumentoRequest $request, Inscricao $inscricao)
    {
        return $this->inscricaoDocumentoService->store($request, $inscricao);
    }

    /**
     * Exibir documento da inscricao.
     *
     * Retorna um documento especifico da inscricao.
     *
     * @urlParam inscricao integer required ID da inscricao. Example: 1
     * @urlParam documento integer required ID do documento. Example: 1
     */
    public function show(string $inscricao, string $documento)
    {
        return $this->inscricaoDocumentoService->show($inscricao, $documento);
    }

    /**
     * Atualizar documento da inscricao.
     *
     * Atualiza os dados ou arquivo de um documento da inscricao.
     *
     * @urlParam inscricao integer required ID da inscricao. Example: 1
     * @urlParam documento integer required ID do documento. Example: 1
     */
    public function update(UpdateDocumentoRequest $request, Inscricao $inscricao, InscricaoDocumento $documento)
    {
        return $this->inscricaoDocumentoService->update($request, $inscricao, $documento);
    }

    /**
     * Remover documento da inscricao.
     *
     * Remove um documento e o arquivo associado.
     *
     * @urlParam inscricao integer required ID da inscricao. Example: 1
     * @urlParam documento integer required ID do documento. Example: 1
     */
    public function destroy(Inscricao $inscricao, InscricaoDocumento $documento)
    {
        return $this->inscricaoDocumentoService->destroy($inscricao, $documento);
    }
}
