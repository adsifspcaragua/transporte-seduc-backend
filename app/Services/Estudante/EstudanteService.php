<?php

namespace App\Services\Estudante;

use App\Http\Resources\Estudante\EstudanteResource;
use App\Models\Estudante;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Throwable;

class EstudanteService
{
    public function index(int $perPage = 10): JsonResponse|AnonymousResourceCollection
    {
        $allowedPerPage = [10, 15, 20, 30];
        $perPage = in_array($perPage, $allowedPerPage, true) ? $perPage : 10;

        $estudantes = Estudante::with(['inscricao.inscricao_instituicao', 'inscricao.inscricao_documentos'])->paginate($perPage);

        if ($estudantes->isEmpty()) {
            return response()->json(['message' => 'Nenhum estudante cadastrado'], 200);
        }

        return EstudanteResource::collection($estudantes);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function store(array $data): JsonResponse
    {
        try {
            $estudante = DB::transaction(function () use ($data) {
                $data['status'] = 'Em espera';

                return Estudante::create($data);
            });

            return response()->json([
                'data' => new EstudanteResource($estudante),
                'message' => 'Estudante criado com sucesso',
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Erro ao cadastrar estudante',
            ], 500);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $estudante = Estudante::with(['inscricao.inscricao_instituicao', 'inscricao.inscricao_documentos'])->find($id);

            if (! $estudante) {
                return response()->json(['message' => 'Estudante não encontrado'], 404);
            }

            return response()->json([
                'data' => new EstudanteResource($estudante),
                'message' => 'Estudante encontrado com sucesso',
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Erro ao encontrar estudante',
            ], 500);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(array $data, string $id): JsonResponse
    {
        try {
            $estudante = DB::transaction(function () use ($data, $id) {
                $estudante = Estudante::with(['inscricao.inscricao_instituicao', 'inscricao.inscricao_documentos'])->find($id);

                if (! $estudante) {
                    return null;
                }

                $estudanteData = array_intersect_key($data, array_flip([
                    'name',
                    'email',
                    'cpf',
                    'birth_date',
                    'phone',
                    'address',
                    'observation',
                    'status',
                    'linha_id',
                    'user_id',
                    'instituicao_id',
                    'inscricao_id',
                ]));

                $inscricaoData = array_intersect_key($data, array_flip([
                    'name',
                    'cpf',
                    'rg',
                    'birth_date',
                    'phone',
                    'email',
                    'cep',
                    'address',
                    'neighborhood',
                    'city',
                    'complement',
                    'number',
                    'father_name',
                    'mother_name',
                    'observation',
                ]));

                $instituicaoData = array_intersect_key($data, array_flip([
                    'course',
                    'semester',
                    'expected_completion',
                    'shift',
                    'city_destination',
                    'used_transport',
                    'days_of_week',
                    'has_scholarship',
                    'scholarship_type',
                    'instituicao_id',
                ]));

                if ($estudanteData !== []) {
                    $estudante->update($estudanteData);
                }

                if ($inscricaoData !== [] && $estudante->inscricao) {
                    $estudante->inscricao->update($inscricaoData);
                }

                if ($instituicaoData !== [] && $estudante->inscricao) {
                    $estudante->inscricao->inscricao_instituicao()->updateOrCreate(
                        ['inscricao_id' => $estudante->inscricao->id],
                        $instituicaoData,
                    );
                }

                return $estudante->refresh()->load('inscricao.inscricao_instituicao');
            });

            if (! $estudante) {
                return response()->json(['message' => 'Estudante não encontrado'], 404);
            }

            return response()->json([
                'data' => new EstudanteResource($estudante),
                'message' => 'Estudante atualizado com sucesso',
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Erro ao atualizar estudante',
            ], 500);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $estudante = Estudante::find($id);

            if (! $estudante) {
                return response()->json(['message' => 'Estudante não encontrado'], 404);
            }

            $estudanteExibir = $estudante;
            $estudante->delete();

            return response()->json([
                'data' => new EstudanteResource($estudanteExibir),
                'message' => 'Estudante deletado com sucesso',
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Erro ao excluir estudante',
            ], 500);
        }
    }

    public function countEstudantes(): JsonResponse
    {
        try {
            return response()->json([
                'data' => Estudante::count(),
                'message' => 'Contagem de estudantes realizada com sucesso',
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Erro ao contar estudantes',
            ], 500);
        }
    }

    public function estudantesAtivos(): JsonResponse
    {
        try {
            $estudantes = Estudante::where('status', 'ATIVO')->paginate(10);

            return response()->json([
                'data' => EstudanteResource::collection($estudantes),
                'message' => 'Estudantes ativos retornados com sucesso',
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Erro ao retornar estudantes ativos',
            ], 500);
        }
    }
}
