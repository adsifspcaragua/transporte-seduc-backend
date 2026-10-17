<?php

namespace App\Services\Viagem;

use App\Http\Resources\Viagem\AutorizacaoLinhaResource;
use App\Models\AutorizacaoLinha;
use App\Models\User;
use App\Services\Auditoria\AuditoriaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Throwable;

/**
 * Remanejamentos temporarios: o estudante usa outra linha por um periodo, sem
 * mudar a linha do cadastro.
 */
class AutorizacaoLinhaService
{
    public function __construct(private readonly AuditoriaService $auditoria) {}

    /**
     * @param  array<string, mixed>  $filtros
     */
    public function index(array $filtros): JsonResponse
    {
        $autorizacoes = AutorizacaoLinha::with(['estudante:id,name,cpf,linha_id', 'linha:id,name', 'criadaPor:id,name'])
            ->when($filtros['estudante_id'] ?? null, fn ($q, $id) => $q->where('estudante_id', $id))
            ->when($filtros['linha_id'] ?? null, fn ($q, $id) => $q->where('linha_id', $id))
            ->when($filtros['data'] ?? null, fn ($q, $data) => $q->cobrindo(Carbon::parse($data)))
            ->orderByDesc('inicio')
            ->get();

        return response()->json(['data' => AutorizacaoLinhaResource::collection($autorizacoes)]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function store(User $user, array $data): JsonResponse
    {
        try {
            $autorizacao = AutorizacaoLinha::create([...$data, 'criada_por' => $user->id]);

            $this->auditoria->registrar($user, 'autorizacao_linha.criar', $autorizacao, [
                'estudante_id' => $autorizacao->estudante_id,
                'linha_id' => $autorizacao->linha_id,
                'inicio' => $autorizacao->inicio->toDateString(),
                'fim' => $autorizacao->fim?->toDateString(),
            ], $autorizacao->motivo);

            return response()->json([
                'data' => new AutorizacaoLinhaResource($autorizacao->load(['estudante:id,name,cpf,linha_id', 'linha:id,name'])),
                'message' => 'Autorização registrada',
            ], 201);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['message' => 'Erro ao registrar autorização'], 500);
        }
    }

    public function destroy(User $user, string $id): JsonResponse
    {
        $autorizacao = AutorizacaoLinha::find($id);

        if (! $autorizacao) {
            return response()->json(['message' => 'Autorização não encontrada'], 404);
        }

        $autorizacao->delete();
        $this->auditoria->registrar($user, 'autorizacao_linha.remover', null, [
            'autorizacao_id' => (int) $id,
            'estudante_id' => $autorizacao->estudante_id,
            'linha_id' => $autorizacao->linha_id,
        ]);

        return response()->json(['message' => 'Autorização removida']);
    }
}
