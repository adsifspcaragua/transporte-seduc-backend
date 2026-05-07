<?php

namespace App\Services\Inscricao\Instituicao;

use App\Http\Resources\Inscricao\Instituicao\InscricaoInstituicaoResource;
use App\Models\Inscricao;
use App\Models\InscricaoInstituicoes;
use App\Services\Inscricao\InscricaoStatusService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Throwable;

class InscricaoInstituicaoService
{
    public function __construct(private readonly InscricaoStatusService $statusService) {}

    public function index(string $inscricaoId): JsonResponse|AnonymousResourceCollection
    {
        $instituicoes = InscricaoInstituicoes::where('inscricao_id', $inscricaoId)->get();

        if ($instituicoes->isEmpty()) {
            return response()->json(['message' => 'Nenhuma inscricao cadastrada'], 200);
        }

        return InscricaoInstituicaoResource::collection($instituicoes);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function store(array $data): JsonResponse
    {
        try {
            $inscricaoInstituicao = InscricaoInstituicoes::create($data);
            $inscricao = Inscricao::find($inscricaoInstituicao->inscricao_id);

            if ($inscricao) {
                $this->statusService->refreshStatus($inscricao);
            }

            return response()->json([
                'data' => new InscricaoInstituicaoResource($inscricaoInstituicao),
                'message' => 'Inscrição criada com sucesso',
            ], 201);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Erro ao criar inscrição.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(string $inscricaoId, string $instituicaoId): JsonResponse
    {
        try {
            $inscricaoInstituicao = $this->findByInscricao($inscricaoId, $instituicaoId);

            if (! $inscricaoInstituicao) {
                return response()->json(['message' => 'Inscricao não encontrada'], 404);
            }

            return response()->json([
                'data' => new InscricaoInstituicaoResource($inscricaoInstituicao),
                'message' => 'Incricao encontrado com sucesso',
            ], 200);
        } catch (Throwable) {
            return response()->json(['message' => 'Erro ao buscar inscrição.'], 500);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(array $data, string $inscricaoId, string $instituicaoId): JsonResponse
    {
        try {
            $inscricaoInstituicao = $this->findByInscricao($inscricaoId, $instituicaoId);

            if (! $inscricaoInstituicao) {
                return response()->json([
                    'message' => 'Inscricao não encontrada',
                ], 404);
            }

            $inscricaoInstituicao->update($data);
            $inscricao = Inscricao::find($inscricaoId);

            if ($inscricao) {
                $this->statusService->refreshStatus($inscricao);
            }

            return response()->json([
                'data' => new InscricaoInstituicaoResource($inscricaoInstituicao),
                'message' => 'Inscricao atualizada com sucesso',
            ], 200);
        } catch (Throwable) {
            return response()->json(['message' => 'Erro ao atualizar inscrição.'], 500);
        }
    }

    public function destroy(string $inscricaoId, string $instituicaoId): JsonResponse
    {
        try {
            $inscricaoInstituicao = $this->findByInscricao($inscricaoId, $instituicaoId);

            if (! $inscricaoInstituicao) {
                return response()->json(['message' => 'Inscricao não encontrada'], 404);
            }

            $inscricaoInstituicao->delete();
            $inscricao = Inscricao::find($inscricaoId);

            if ($inscricao) {
                $this->statusService->refreshStatus($inscricao);
            }

            return response()->json(['message' => 'Inscricao deletada com sucesso']);
        } catch (Throwable) {
            return response()->json(['message' => 'Erro ao deletar inscrição.'], 500);
        }
    }

    private function findByInscricao(string $inscricaoId, string $instituicaoId): ?InscricaoInstituicoes
    {
        return InscricaoInstituicoes::where('inscricao_id', $inscricaoId)
            ->whereKey($instituicaoId)
            ->first();
    }
}
