<?php

namespace App\Http\Controllers\Api\Estudante;

use App\Http\Controllers\Controller;
use App\Models\Estudante;
use App\Services\Estudante\ContaEstudanteService;
use Illuminate\Http\Request;

/**
 * @group Estudantes
 *
 * @authenticated
 */
class ContaEstudanteController extends Controller
{
    public function __construct(private readonly ContaEstudanteService $service) {}

    /**
     * Criar conta do estudante.
     *
     * Cria o login com o e-mail e o CPF do cadastro, no perfil estudante, e envia
     * o link para o estudante definir a senha.
     *
     * @urlParam estudante integer required ID do estudante. Example: 1
     */
    public function store(Request $request, Estudante $estudante)
    {
        return $this->service->criar($request->user(), $estudante);
    }
}
