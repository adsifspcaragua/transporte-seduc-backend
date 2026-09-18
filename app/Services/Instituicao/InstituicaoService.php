<?php

namespace App\Services\Instituicao;

use App\Http\Resources\Instituicao\InstituicaoResource;
use App\Models\Instituicao;
use Illuminate\Http\JsonResponse;
use Throwable;

class InstituicaoService
{
    public function index(): JsonResponse
    {
        $instituicoes = Instituicao::paginate(15);

        if ($instituicoes->isEmpty()) {
            return response()->json(['message' => 'Nenhuma instituição cadastrada'], 200);
        }

        return response()->json([
            'data' => InstituicaoResource::collection($instituicoes),
            'message' => 'Instituição encontrada com sucesso',
        ], 200);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function store(array $data): JsonResponse
    {
        try {
            $instituicao = Instituicao::create($data);

            return response()->json([
                'data' => new InstituicaoResource($instituicao),
                'message' => 'Instituição criada com sucesso',
            ], 200);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Erro ao cadastrar instituição',
            ], 500);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $instituicao = Instituicao::find($id);

            if (! $instituicao) {
                return response()->json(['message' => 'Instituição não encontrada'], 404);
            }

            return response()->json([
                'data' => new InstituicaoResource($instituicao),
                'message' => 'Instituição encontrada com sucesso',
            ], 200);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Erro ao encontrar instituição',
            ], 500);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(array $data, string $id): JsonResponse
    {
        try {
            $instituicao = Instituicao::find($id);

            if (! $instituicao) {
                return response()->json(['message' => 'Instituição não encontrada'], 404);
            }

            $instituicao->update($data);

            return response()->json([
                'data' => new InstituicaoResource($instituicao),
                'message' => 'Instituição atualizada com sucesso',
            ], 200);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Erro ao atualizar instituição',
            ], 500);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $instituicao = Instituicao::find($id);

            if (! $instituicao) {
                return response()->json([
                    'message' => 'Instituição não encontrada',
                ], 404);
            }

            if ($instituicao->inscricoesInstituicoes()->exists() || $instituicao->estudantesInstituicoes()->exists()) {
                return response()->json([
                    'message' => 'Esta instituição ainda está vinculada a estudantes ou inscrições e não pode ser removida.',
                ]);
            }

            $instituicaoExibir = $instituicao;
            $instituicao->delete();

            return response()->json([
                'data' => new InstituicaoResource($instituicaoExibir),
                'message' => 'Instituição deletada com sucesso',
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Erro ao excluir instituição',
            ], 500);
        }
    }
}
