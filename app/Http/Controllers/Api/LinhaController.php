<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Linha\StoreLinhaRequest;
use App\Http\Requests\Linha\UpdateLinhaRequest;
use App\Models\Linha;
use App\Services\Linha\LinhaService;
use Illuminate\Http\Request;

/**
 * @group Linhas
 *
 * Rotas para gerenciamento das linhas de transporte.
 *
 * @authenticated
 */
class LinhaController extends Controller
{
    public function __construct(private readonly LinhaService $linhaService) {}

    /**
     * Listar linhas.
     *
     * Retorna todas as linhas cadastradas.
     */
    public function index()
    {
        return $this->linhaService->index();
    }

    /** Listar estudantes vinculados a uma linha, com paginacao. */
    public function estudantes(Request $request, Linha $linha)
    {
        $data = $request->validate([
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'in:10,15,20,30'],
        ]);

        return $this->linhaService->estudantes($linha, (int) ($data['per_page'] ?? 10));
    }

    /**
     * Cadastrar linha.
     *
     * Cria uma nova linha de transporte.
     */
    public function store(StoreLinhaRequest $request)
    {
        return $this->linhaService->store($request->validated());
    }

    /**
     * Exibir linha.
     *
     * Retorna os dados de uma linha especifica.
     *
     * @urlParam linha integer required ID da linha. Example: 1
     */
    public function show(string $id)
    {
        return $this->linhaService->show($id);
    }

    /**
     * Atualizar linha.
     *
     * Atualiza os dados de uma linha existente.
     *
     * @urlParam linha integer required ID da linha. Example: 1
     */
    public function update(UpdateLinhaRequest $request, string $id)
    {
        return $this->linhaService->update($request->validated(), $id);
    }

    /**
     * Remover linha.
     *
     * Remove uma linha cadastrada.
     *
     * @urlParam linha integer required ID da linha. Example: 1
     */
    public function destroy(string $id)
    {
        return $this->linhaService->destroy($id);
    }
}
