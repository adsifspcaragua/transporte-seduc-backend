<?php

namespace App\Services\Curso;

use App\Http\Resources\Curso\CursoResource;
use App\Models\Curso;
use Illuminate\Http\JsonResponse;

class CursoService
{
    public function index(): JsonResponse
    {
        $cursos = Curso::paginate(15);

        if ($cursos->isEmpty()) {
            return response()->json(['message' => 'Nenhum curso cadastrado'], 200);
        }

        return response()->json([
            'data' => CursoResource::collection($cursos),
            'message' => 'Curso encontrado com sucesso',
        ], 200);
    }
}
