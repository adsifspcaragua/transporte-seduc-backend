<?php

namespace App\Services\Reecadastro;

use App\Http\Resources\Reecadastro\Solicitacao\SolicitacaoResource;
use App\Models\Estudante;
use App\Models\SolicitacaoReecadastro;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Throwable;

class SolicitacaoReecadastroService
{
    public function index(): JsonResponse|AnonymousResourceCollection
    {
        $query = SolicitacaoReecadastro::query();

        if ($this->isEstudanteScope()) {
            $query->whereIn('estudante_id', $this->ownEstudanteIds());
        }

        $solicitacoes = $query->get();

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
            if ($this->isEstudanteScope()) {
                $estudanteId = $this->ownEstudanteIds()->first();

                if (! $estudanteId) {
                    return response()->json(['message' => 'Nenhum estudante vinculado ao usuário.'], 403);
                }

                $data['estudante_id'] = $estudanteId;
            }

            $solicitacao = SolicitacaoReecadastro::create($data);

            return response()->json([
                'data' => new SolicitacaoResource($solicitacao),
                'message' => 'Solicitação de recadastro criada com sucesso',
            ], 201);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Erro ao cadastrar solicitação de recadastro',
            ], 500);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $solicitacao = SolicitacaoReecadastro::find($id);

            if (! $solicitacao) {
                return response()->json(['message' => 'Solicitação de recadastro não encontrada'], 404);
            }

            if (! $this->canAccess($solicitacao)) {
                return response()->json(['message' => 'Acesso negado.'], 403);
            }

            return response()->json([
                'data' => new SolicitacaoResource($solicitacao),
                'message' => 'Solicitação de recadastro encontrada com sucesso',
            ], 200);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Erro ao encontrar solicitação de recadastro',
            ], 500);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(array $data, string $id): JsonResponse
    {
        try {
            $solicitacao = SolicitacaoReecadastro::find($id);

            if (! $solicitacao) {
                return response()->json(['message' => 'Solicitação de recadastro não encontrada'], 404);
            }

            if (! $this->canAccess($solicitacao)) {
                return response()->json(['message' => 'Acesso negado.'], 403);
            }

            $solicitacao->update($data);

            return response()->json([
                'data' => new SolicitacaoResource($solicitacao),
                'message' => 'Solicitação de recadastro atualizada com sucesso',
            ], 200);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Erro ao atualizar solicitação de recadastro',
            ], 500);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $solicitacao = SolicitacaoReecadastro::find($id);

            if (! $solicitacao) {
                return response()->json(['message' => 'Solicitação de recadastro não encontrada'], 404);
            }

            $solicitacao->delete();

            return response()->json([
                'message' => 'Solicitação de recadastro deletada com sucesso',
            ], 200);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Erro ao deletar solicitação de recadastro',
            ], 500);
        }
    }

    /**
     * Indica se o usuário autenticado deve ser restringido às próprias solicitações
     * (perfil "estudante" sem perfil administrativo).
     */
    private function isEstudanteScope(): bool
    {
        $user = Auth::user();

        return $user
            && $user->hasRole('estudante')
            && ! $user->hasRole('admin', 'gestor', 'operador');
    }

    /**
     * IDs dos estudantes vinculados ao usuário autenticado.
     *
     * @return Collection<int, int>
     */
    private function ownEstudanteIds(): Collection
    {
        return Estudante::where('user_id', Auth::id())->pluck('id');
    }

    private function canAccess(SolicitacaoReecadastro $solicitacao): bool
    {
        if (! $this->isEstudanteScope()) {
            return true;
        }

        return $this->ownEstudanteIds()->contains($solicitacao->estudante_id);
    }
}
