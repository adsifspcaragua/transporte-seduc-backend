<?php

namespace App\Http\Controllers\Api\Reecadastro;

use App\Http\Controllers\Controller;
use App\Services\Reecadastro\DocumentoReecadastroService;
use Illuminate\Http\Request;

/**
 * @group Recadastro
 *
 * Documentos enviados pelos estudantes no recadastro. Os arquivos ficam em
 * disco privado e so podem ser acessados pela rota de download.
 *
 * @authenticated
 */
class DocumentoReecadastroController extends Controller
{
    public function __construct(private readonly DocumentoReecadastroService $documentoReecadastroService) {}

    /**
     * Listar documentos de recadastro.
     *
     * @queryParam solicitacao_id integer Filtra pelos documentos de uma solicitacao. Example: 1
     * @queryParam estudante_id integer Filtra pelos documentos de um estudante. Example: 1
     */
    public function index(Request $request)
    {
        return $this->documentoReecadastroService->index(
            $request->only(['solicitacao_id', 'estudante_id']),
        );
    }

    /**
     * Exibir documento de recadastro.
     *
     * @urlParam documento integer required ID do documento de recadastro. Example: 1
     */
    public function show(string $id)
    {
        return $this->documentoReecadastroService->show($id);
    }

    /**
     * Baixar documento de recadastro.
     *
     * Devolve o arquivo enviado pelo estudante.
     *
     * @urlParam documento integer required ID do documento de recadastro. Example: 1
     */
    public function download(string $id)
    {
        return $this->documentoReecadastroService->download($id);
    }

    /**
     * Remover documento de recadastro.
     *
     * @urlParam documento integer required ID do documento de recadastro. Example: 1
     */
    public function destroy(string $id)
    {
        return $this->documentoReecadastroService->destroy($id);
    }
}
