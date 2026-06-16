<?php

namespace App\Http\Controllers\Api\Curso;

use App\Http\Controllers\Controller;
use App\Services\Curso\CursoService;

/**
 * @group Cursos
 *
 * Rotas para listagem dos cursos.
 */
class CursoController extends Controller
{
    public function __construct(private readonly CursoService $cursoService) {}

    /**
     * Listar cursos.
     *
     * Retorna a lista paginada de cursos cadastrados.
     */
    public function index()
    {
        return $this->cursoService->index();
    }
}
