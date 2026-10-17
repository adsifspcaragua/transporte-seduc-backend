<?php

namespace App\Services\Viagem;

use App\Http\Resources\Viagem\LinhaPontoResource;
use App\Http\Resources\Viagem\PontoEmbarqueResource;
use App\Models\LinhaPonto;
use App\Models\PontoEmbarque;
use App\Models\Presenca;
use App\Models\User;
use App\Services\Auditoria\AuditoriaService;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Throwable;

/**
 * Pontos fixos de embarque, a geocerca de cada um e em quais linhas/sentidos
 * sao usados (Alternativa A do roadmap).
 */
class PontoEmbarqueService
{
    public function __construct(private readonly AuditoriaService $auditoria) {}

    public function index(): JsonResponse
    {
        $pontos = PontoEmbarque::with('linhas.linha:id,name')->orderBy('nome')->get();

        return response()->json(['data' => PontoEmbarqueResource::collection($pontos)]);
    }

    public function show(string $id): JsonResponse
    {
        $ponto = PontoEmbarque::with('linhas.linha:id,name')->find($id);

        if (! $ponto) {
            return response()->json(['message' => 'Ponto não encontrado'], 404);
        }

        return response()->json(['data' => new PontoEmbarqueResource($ponto)]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function store(User $user, array $data): JsonResponse
    {
        try {
            $ponto = PontoEmbarque::create($data);
            $this->auditoria->registrar($user, 'ponto.criar', $ponto, $ponto->only(['nome', 'raio_metros']));

            return response()->json([
                'data' => new PontoEmbarqueResource($ponto),
                'message' => 'Ponto cadastrado com sucesso',
            ], 201);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['message' => 'Erro ao cadastrar ponto'], 500);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(User $user, string $id, array $data): JsonResponse
    {
        try {
            $ponto = PontoEmbarque::find($id);

            if (! $ponto) {
                return response()->json(['message' => 'Ponto não encontrado'], 404);
            }

            $antes = $ponto->only(array_keys($data));
            $ponto->update($data);

            // O raio e a posicao mudam o que e aceito: ficam na trilha.
            $this->auditoria->registrar($user, 'ponto.atualizar', $ponto, ['antes' => $antes, 'depois' => $ponto->only(array_keys($data))]);

            return response()->json([
                'data' => new PontoEmbarqueResource($ponto->load('linhas.linha:id,name')),
                'message' => 'Ponto atualizado com sucesso',
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['message' => 'Erro ao atualizar ponto'], 500);
        }
    }

    public function destroy(User $user, string $id): JsonResponse
    {
        $ponto = PontoEmbarque::find($id);

        if (! $ponto) {
            return response()->json(['message' => 'Ponto não encontrado'], 404);
        }

        if (Presenca::where('ponto_id', $ponto->id)->exists()) {
            return response()->json([
                'message' => 'Já há presenças registradas neste ponto. Inative-o em vez de excluir.',
            ], 409);
        }

        $ponto->delete();
        $this->auditoria->registrar($user, 'ponto.excluir', null, ['ponto_id' => (int) $id, 'nome' => $ponto->nome]);

        return response()->json(['message' => 'Ponto excluído com sucesso']);
    }

    /**
     * @param  array<string, mixed>  $filtros
     */
    public function vinculos(array $filtros): JsonResponse
    {
        $vinculos = LinhaPonto::with(['linha:id,name', 'ponto'])
            ->when($filtros['linha_id'] ?? null, fn ($q, $id) => $q->where('linha_id', $id))
            ->when($filtros['sentido'] ?? null, fn ($q, $sentido) => $q->where('sentido', $sentido))
            ->orderBy('linha_id')
            ->orderBy('sentido')
            ->orderBy('ordem')
            ->get();

        return response()->json(['data' => LinhaPontoResource::collection($vinculos)]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function vincular(User $user, array $data): JsonResponse
    {
        try {
            $vinculo = LinhaPonto::create($data);
            $this->auditoria->registrar($user, 'ponto.vincular', $vinculo, $vinculo->only(['linha_id', 'ponto_id', 'sentido', 'horario_previsto']));

            return response()->json([
                'data' => new LinhaPontoResource($vinculo->load(['linha:id,name', 'ponto'])),
                'message' => 'Ponto vinculado à linha',
            ], 201);
        } catch (UniqueConstraintViolationException) {
            return response()->json(['message' => 'Este ponto já está vinculado a esta linha neste sentido.'], 409);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['message' => 'Erro ao vincular ponto'], 500);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function atualizarVinculo(User $user, string $id, array $data): JsonResponse
    {
        $vinculo = LinhaPonto::find($id);

        if (! $vinculo) {
            return response()->json(['message' => 'Vínculo não encontrado'], 404);
        }

        $antes = $vinculo->only(array_keys($data));
        $vinculo->update($data);
        $this->auditoria->registrar($user, 'ponto.vinculo.atualizar', $vinculo, ['antes' => $antes, 'depois' => $vinculo->only(array_keys($data))]);

        return response()->json([
            'data' => new LinhaPontoResource($vinculo->load(['linha:id,name', 'ponto'])),
            'message' => 'Vínculo atualizado',
        ]);
    }

    public function desvincular(User $user, string $id): JsonResponse
    {
        $vinculo = LinhaPonto::find($id);

        if (! $vinculo) {
            return response()->json(['message' => 'Vínculo não encontrado'], 404);
        }

        $vinculo->delete();
        $this->auditoria->registrar($user, 'ponto.desvincular', null, $vinculo->only(['linha_id', 'ponto_id', 'sentido']));

        return response()->json(['message' => 'Ponto desvinculado da linha']);
    }
}
