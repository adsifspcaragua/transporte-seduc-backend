<?php

namespace App\Http\Controllers\Api\Auditoria;

use App\Http\Controllers\Controller;
use App\Services\Auditoria\AuditoriaService;
use Illuminate\Http\Request;

/**
 * @group Presenca - Gestao
 *
 * @authenticated
 */
class AuditoriaController extends Controller
{
    public function __construct(private readonly AuditoriaService $service) {}

    /**
     * Trilha de auditoria.
     *
     * Acoes administrativas sensiveis do modulo de presenca, das mais recentes
     * para as mais antigas. `acao` filtra pelo prefixo (ex.: "dispositivo.").
     */
    public function index(Request $request)
    {
        $filtros = $request->validate([
            'acao' => 'sometimes|string|max:60',
            'user_id' => 'sometimes|integer',
            'de' => 'sometimes|date_format:Y-m-d',
            'ate' => 'sometimes|date_format:Y-m-d',
            'per_page' => 'sometimes|integer|in:10,15,20,30',
        ]);

        return $this->service->index($filtros);
    }
}
