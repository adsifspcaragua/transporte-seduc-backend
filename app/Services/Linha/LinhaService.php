<?php

namespace App\Services\Linha;

use App\Http\Resources\Linha\LinhaEstudanteResource;
use App\Http\Resources\Linha\LinhaResource;
use App\Models\Linha;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Throwable;

class LinhaService
{
    public function estudantes(Linha $linha, int $perPage = 10): AnonymousResourceCollection
    {
        $estudantes = $linha->estudantes()
            ->whereRaw('LOWER(status) = ?', ['ativo'])
            ->with([
                'instituicao:id,name',
                'inscricao:id,name,email,phone',
                'inscricao.inscricao_instituicao',
            ])
            ->orderBy('name')
            ->orderBy('id')
            ->paginate($perPage);

        return LinhaEstudanteResource::collection($estudantes);
    }

    public function index(): JsonResponse
    {
        try {
            $linhas = Linha::with('motorista:id,name')->withCount([
                'estudantes as ocupacao' => fn ($query) => $query->where('status', 'Ativo'),
            ])->get();

            if ($linhas->isEmpty()) {
                return response()->json(['message' => 'Nenhuma linha cadastrada'], 200);
            }

            return response()->json([
                'data' => LinhaResource::collection($linhas),
                'message' => 'Linhas encontradas com sucesso',
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
                'data' => new LinhaResource($linha->load('motorista:id,name')),
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
            $linha = Linha::with('motorista:id,name')->find($id);

            if (! $linha) {
                return response()->json(['message' => 'Linha não encontrada'], 404);
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
                'data' => new LinhaResource($linha->load('motorista:id,name')),
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

            // Nao ha chave estrangeira em estudantes.linha_id: apagar a linha
            // deixaria os estudantes apontando para uma linha inexistente, sem
            // ninguem perceber. Realocar e decisao da responsavel.
            $vinculados = $linha->estudantes()->count();

            if ($vinculados > 0) {
                return response()->json([
                    'message' => "Esta linha tem {$vinculados} estudante(s) vinculado(s). Realoque-os antes de excluir.",
                ], 409);
            }

            // Apagar a linha levaria junto as chamadas (cascata) e, com elas, a
            // frequencia de todos que ja andaram nela.
            $chamadas = $linha->chamadas()->count();

            if ($chamadas > 0) {
                return response()->json([
                    'message' => "Esta linha tem {$chamadas} chamada(s) registrada(s) e não pode ser excluída sem perder o histórico de frequência.",
                ], 409);
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
