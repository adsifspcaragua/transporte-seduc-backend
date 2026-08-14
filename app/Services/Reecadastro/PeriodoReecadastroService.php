<?php

namespace App\Services\Reecadastro;

use App\Http\Resources\Reecadastro\Periodo\PeriodoResource;
use App\Models\PeriodoReecadastro;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Throwable;

class PeriodoReecadastroService
{
    public function index(): JsonResponse|AnonymousResourceCollection
    {
        $periodos = PeriodoReecadastro::orderByDesc('ano')->orderByDesc('semestre')->get();

        if ($periodos->isEmpty()) {
            return response()->json(['message' => 'Nenhum período de recadastro cadastrado'], 200);
        }

        return PeriodoResource::collection($periodos);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function store(array $data): JsonResponse
    {
        try {
            $periodo = PeriodoReecadastro::create([...$data, 'status' => 'Fechado']);

            return response()->json([
                'data' => new PeriodoResource($periodo),
                'message' => 'Período de recadastro criado com sucesso',
            ], 201);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Erro ao cadastrar período de recadastro',
            ], 500);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $periodo = PeriodoReecadastro::find($id);

            if (! $periodo) {
                return response()->json(['message' => 'Período de recadastro não encontrado'], 404);
            }

            return response()->json([
                'data' => new PeriodoResource($periodo),
                'message' => 'Período de recadastro encontrado com sucesso',
            ], 200);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Erro ao encontrar período de recadastro',
            ], 500);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(array $data, string $id): JsonResponse
    {
        try {
            $periodo = PeriodoReecadastro::find($id);

            if (! $periodo) {
                return response()->json(['message' => 'Período de recadastro não encontrado'], 404);
            }

            $periodo->update($data);

            return response()->json([
                'data' => new PeriodoResource($periodo->refresh()),
                'message' => 'Período de recadastro atualizado com sucesso',
            ], 200);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Erro ao atualizar período de recadastro',
            ], 500);
        }
    }

    /**
     * Libera o recadastro para os estudantes. Só um período fica aberto por
     * vez: abrir um novo fecha automaticamente o anterior.
     */
    public function abrir(string $id): JsonResponse
    {
        try {
            $periodo = PeriodoReecadastro::find($id);

            if (! $periodo) {
                return response()->json(['message' => 'Período de recadastro não encontrado'], 404);
            }

            PeriodoReecadastro::where('status', 'Aberto')
                ->whereKeyNot($periodo->id)
                ->update(['status' => 'Fechado']);

            $periodo->update(['status' => 'Aberto']);

            return response()->json([
                'data' => new PeriodoResource($periodo->refresh()),
                'message' => 'Período de recadastro aberto com sucesso',
            ], 200);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Erro ao abrir período de recadastro',
            ], 500);
        }
    }

    public function fechar(string $id): JsonResponse
    {
        try {
            $periodo = PeriodoReecadastro::find($id);

            if (! $periodo) {
                return response()->json(['message' => 'Período de recadastro não encontrado'], 404);
            }

            $periodo->update(['status' => 'Fechado']);

            return response()->json([
                'data' => new PeriodoResource($periodo->refresh()),
                'message' => 'Período de recadastro fechado com sucesso',
            ], 200);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Erro ao fechar período de recadastro',
            ], 500);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $periodo = PeriodoReecadastro::withCount('solicitacoes')->find($id);

            if (! $periodo) {
                return response()->json(['message' => 'Período de recadastro não encontrado'], 404);
            }

            if ($periodo->solicitacoes_count > 0) {
                return response()->json([
                    'message' => 'O período possui solicitações e não pode ser removido',
                ], 409);
            }

            $periodo->delete();

            return response()->json([
                'message' => 'Período de recadastro deletado com sucesso',
            ], 200);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Erro ao deletar período de recadastro',
            ], 500);
        }
    }
}
