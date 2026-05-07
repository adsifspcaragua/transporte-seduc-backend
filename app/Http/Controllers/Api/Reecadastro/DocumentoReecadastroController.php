<?php

namespace App\Http\Controllers\Api\Reecadastro;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reecadastro\Documento\StoreDocumentoRequest;
use App\Http\Requests\Reecadastro\Documento\UpdateDocumentoRequest;
use App\Services\Reecadastro\DocumentoReecadastroService;

/**
 * @group Recadastro
 *
 * Rotas para documentos e solicitacoes de recadastro dos estudantes.
 *
 * @authenticated
 */
class DocumentoReecadastroController extends Controller
{
    public function __construct(private readonly DocumentoReecadastroService $documentoReecadastroService) {}

    /**
     * Listar documentos de recadastro.
     *
     * Retorna todos os documentos enviados para recadastro.
     */
    public function index()
    {
        return $this->documentoReecadastroService->index();
    }

    /**
     * Cadastrar documento de recadastro.
     *
     * Registra um documento vinculado ao recadastro de um estudante.
     */
    public function store(StoreDocumentoRequest $request)
    {
        return $this->documentoReecadastroService->store($request->validated());
    }

    /**
     * Exibir documento de recadastro.
     *
     * Retorna um documento de recadastro especifico.
     *
     * @urlParam documento integer required ID do documento de recadastro. Example: 1
     */
    public function show(string $id)
    {
        return $this->documentoReecadastroService->show($id);
    }

    /**
     * Atualizar documento de recadastro.
     *
     * Atualiza os dados de um documento de recadastro.
     *
     * @urlParam documento integer required ID do documento de recadastro. Example: 1
     */
    public function update(UpdateDocumentoRequest $request, string $id)
    {
        return $this->documentoReecadastroService->update($request->validated(), $id);
    }

    /**
     * Remover documento de recadastro.
     *
     * Remove um documento de recadastro.
     *
     * @urlParam documento integer required ID do documento de recadastro. Example: 1
     */
    public function destroy(string $id)
    {
        return $this->documentoReecadastroService->destroy($id);
    }
}
