<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;

/**
 * @group Dashboard
 *
 * Numeros da tela inicial.
 *
 * @authenticated
 */
class DashboardController extends Controller
{
    public function __construct(private readonly DashboardService $service) {}

    /**
     * Resumo do sistema.
     *
     * Contadores de estudantes, inscricoes, recadastro e ocupacao das linhas.
     */
    public function index()
    {
        return $this->service->index();
    }
}
