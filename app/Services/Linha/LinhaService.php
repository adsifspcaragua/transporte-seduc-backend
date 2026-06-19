<?php

namespace App\Services\Linha;

use App\Http\Resources\Linha\LinhaResource;
use App\Models\Linha;
use Illuminate\Http\JsonResponse;
use Throwable;

class LinhaService
{
    public function index(): JsonResponse
    {
        try {
            $linhas = Linha::all();

            if ($linhas->isEmpty()) {
                return response()->json(['message' => 'Nenhuma linha cadastrada'], 200);
            }

            return response()->json([
                'data' => LinhaResource::collection($linhas),
                'message' => 'Instituição criada com sucesso',
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Erro ao encontrar linhas',
            ], 500);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function store(array $data): JsonResponse
    {
        try {
            $linha = Linha::create($data);

            return response()->json([
                'data' => new LinhaResource($linha),
                'message' => 'Linha criada com sucesso',
            ], 200);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Erro ao cadastrar linha',
            ], 500);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $linha = Linha::find($id);

            if (! $linha) {
                return response()->json(['message' => 'Linha não encontrada'], 404);
            }

            return response()->json([
                'data' => new LinhaResource($linha),
                'message' => 'Linha encontrada com sucesso',
            ], 200);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Erro ao encontrar linha',
            ], 500);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(array $data, string $id): JsonResponse
    {
        try {
            $linha = Linha::find($id);

            if (! $linha) {
                return response()->json(['message' => 'Linha não encontrada'], 404);
            }

            $linha->update($data);

            return response()->json([
                'data' => new LinhaResource($linha),
                'message' => 'Linha atualizada com sucesso',
            ], 200);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Erro ao atualizar linha',
            ], 500);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $linha = Linha::find($id);

            if (! $linha) {
                return response()->json([
                    'message' => 'Linha não encontrada',
                ], 404);
            }

            $linhaExibir = $linha;
            $linha->delete();

            return response()->json([
                'data' => new LinhaResource($linhaExibir),
                'message' => 'Linha deletada com sucesso',
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Erro ao excluir linha',
            ], 500);
        }
    }
}
