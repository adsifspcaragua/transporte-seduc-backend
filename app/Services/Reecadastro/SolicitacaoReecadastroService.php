<?php

namespace App\Services\Reecadastro;

use App\Http\Resources\Reecadastro\Solicitacao\SolicitacaoResource;
use App\Models\SolicitacaoReecadastro;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Throwable;

class SolicitacaoReecadastroService
{
    public function index(): JsonResponse|AnonymousResourceCollection
    {
        $solicitacoes = SolicitacaoReecadastro::all();

        if ($solicitacoes->isEmpty()) {
            return response()->json(['message' => 'Nenhuma solicitação de recadastro cadastrada'], 200);
        }

        return SolicitacaoResource::collection($solicitacoes);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function store(array $data): JsonResponse
    {
        try {
            $solicitacao = SolicitacaoReecadastro::create($data);

            return response()->json([
                'data' => new SolicitacaoResource($solicitacao),
                'message' => 'Solicitação de recadastro criada com sucesso',
            ], 201);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Erro ao cadastrar solicitação de recadastro',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $solicitacao = SolicitacaoReecadastro::find('id',$id);

            if (! $solicitacao) {
                return response()->json(['message' => 'Solicitação de recadastro não encontrada'], 404);
            }

            return response()->json([
                'data' => new SolicitacaoResource($solicitacao),
                'message' => 'Solicitação de recadastro encontrada com sucesso',
            ], 200);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Erro ao encontrar solicitação de recadastro',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(array $data, string $id): JsonResponse
    {
        try {
            $solicitacao = SolicitacaoReecadastro::find('id',$id);

            if (! $solicitacao) {
                return response()->json(['message' => 'Solicitação de recadastro não encontrada'], 404);
            }

            $solicitacao->update($data);

            return response()->json([
                'data' => new SolicitacaoResource($solicitacao),
                'message' => 'Solicitação de recadastro atualizada com sucesso',
            ], 200);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Erro ao atualizar solicitação de recadastro',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $solicitacao = SolicitacaoReecadastro::find('id',$id);

            if (! $solicitacao) {
                return response()->json(['message' => 'Solicitação de recadastro não encontrada'], 404);
            }

            $solicitacao->delete();

            return response()->json([
                'message' => 'Solicitação de recadastro deletada com sucesso',
            ], 200);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Erro ao deletar solicitação de recadastro',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
