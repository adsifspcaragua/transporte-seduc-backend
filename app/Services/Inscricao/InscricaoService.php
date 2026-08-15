<?php

namespace App\Services\Inscricao;

use App\Http\Resources\Inscricao\InscricaoResource;
use App\Models\Estudante;
use App\Models\Inscricao;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class InscricaoService
{
    public function __construct(private readonly InscricaoStatusService $statusService) {}

    public function index(): JsonResponse|AnonymousResourceCollection
    {
        try {
            $inscricoes = Inscricao::all();

            if ($inscricoes->isEmpty()) {
                return response()->json(['message' => 'Nenhuma inscricao cadastrada'], 200);
            }

            return InscricaoResource::collection($inscricoes);
        } catch (Throwable) {
            return response()->json([
                'message' => 'Nenhuma inscrição encontrada',
            ], 404);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function store(array $data): JsonResponse
    {
        try {
            // Credencial devolvida ao estudante: e o que permite consultar e
            // corrigir a propria inscricao depois, ja que ele nao faz login.
            $inscricao = Inscricao::create([...$data, 'access_token' => Str::random(64)]);
            $inscricao = $this->statusService->refreshStatus($inscricao);

            return response()->json(new InscricaoResource($inscricao), 201);
        } catch (Throwable $ex) {
            report($ex);

            return response()->json([
                'message' => 'Falha ao criar inscrição',
            ], 500);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $inscricao = Inscricao::find($id);

            if (! $inscricao) {
                return response()->json(['message' => 'Inscricao não encontrada'], 404);
            }

            return response()->json([
                'data' => new InscricaoResource($inscricao),
                'message' => 'Incricao encontrado com sucesso',
            ], 200);
        } catch (Throwable $ex) {
            report($ex);

            return response()->json(['message' => 'Erro ao buscar inscrição.',
            ], 500);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(array $data, string $id): JsonResponse
    {
        try {
            $inscricao = Inscricao::find($id);

            if (! $inscricao) {
                return response()->json([
                    'message' => 'Incrição não encontrada',
                ], 404);
            }

            if ($inscricao->status === 'Em analise') {
                return response()->json([
                    'message' => 'A inscrição já está em analise',
                ], 403);
            }

            if (in_array($inscricao->status, ['Aprovado', 'Rejeitado'], true)) {
                return response()->json([
                    'message' => 'A inscrição já foi analisada',
                ], 403);
            }

            $inscricao->update($data);

            $inscricao = $this->statusService->refreshStatus($inscricao);

            return response()->json([
                'data' => new InscricaoResource($inscricao),
                'message' => 'Inscricao atualizada com sucesso',
            ], 200);
        } catch (Throwable) {
            return response()->json([
                'message' => 'Falha ao atualizar inscricao',
            ], 500);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $inscricao = Inscricao::find($id);

            if (! $inscricao) {
                return response()->json(['message' => 'Inscricao não encontrada'], 404);
            }

            $inscricao->delete();

            return response()->json([
                'message' => 'Inscricao deletada com sucesso',
            ], 200);
        } catch (Throwable) {
            return response()->json([
                'message' => 'Falha ao deletar inscricao',
            ], 500);
        }
    }

    /**
     * Registra a decisão da responsável sobre uma inscrição da lista de espera.
     *
     * Aprovar cria (ou atualiza) o estudante correspondente, que passa a estar
     * apto ao benefício; recusar guarda o motivo e inativa o estudante caso ele
     * já existisse.
     *
     * @param  array<string, mixed>  $data
     */
    public function analiseInscricao(string $id, array $data): JsonResponse
    {
        try {
            $inscricao = Inscricao::with([
                'inscricao_instituicao',
                'inscricao_documentos',
                'estudante',
            ])->find($id);

            if (! $inscricao) {
                return response()->json([
                    'message' => 'Inscrição não encontrada',
                ], 404);
            }

            if ($data['decisao'] !== 'Aprovado') {
                $inscricao->update([
                    'status' => 'Rejeitado',
                    'observation' => $data['motivo'],
                ]);
                $inscricao->inscricao_documentos()->update(['status' => 'Rejeitado']);

                $inscricao->estudante?->update(['status' => 'Inativo']);

                return response()->json([
                    'message' => 'Inscrição rejeitada',
                ], 200);
            }

            if (! $this->statusService->isComplete($inscricao)) {
                return response()->json([
                    'message' => 'A inscrição ainda possui dados ou documentos obrigatórios pendentes.',
                ], 422);
            }

            $dadosInstitucionais = $inscricao->inscricao_instituicao;
            $instituicaoId = $dadosInstitucionais?->instituicao_id;

            // Sem esses dados o estudante não pode ser criado (colunas obrigatórias),
            // e a aprovação seria registrada sem gerar o estudante.
            $faltando = array_keys(array_filter([
                'instituicao' => $instituicaoId === null,
                'data de nascimento' => $inscricao->birth_date === null,
                'telefone' => $inscricao->phone === null,
                'endereço' => $inscricao->address === null,
            ]));

            if ($faltando !== [] && ! $inscricao->estudante) {
                return response()->json([
                    'message' => 'Inscrição incompleta para aprovação. Falta: '.implode(', ', $faltando),
                ], 422);
            }

            DB::transaction(function () use ($inscricao, $dadosInstitucionais, $instituicaoId) {
                $inscricao->update([
                    'status' => 'Aprovado',
                    'observation' => null,
                ]);
                $inscricao->inscricao_documentos()->update(['status' => 'Aprovado']);

                $estudanteData = [
                    'name' => $inscricao->name,
                    'email' => $inscricao->email,
                    'cpf' => $inscricao->cpf,
                    'birth_date' => $inscricao->birth_date,
                    'phone' => $inscricao->phone,
                    'address' => $inscricao->address,
                    'days_of_week' => $dadosInstitucionais?->days_of_week ?? [],
                    'observation' => $inscricao->observation,
                    'status' => 'Ativo',
                ];

                if ($instituicaoId !== null) {
                    $estudanteData['instituicao_id'] = $instituicaoId;
                }

                if ($inscricao->estudante) {
                    $inscricao->estudante->update($estudanteData);

                    return;
                }

                Estudante::create([
                    ...$estudanteData,
                    'inscricao_id' => $inscricao->id,
                ]);
            });

            return response()->json([
                'message' => 'Inscrição aprovada',
            ], 200);
        } catch (Throwable $ex) {
            report($ex);

            return response()->json([
                'message' => 'Falha ao registrar decisão',
            ], 500);
        }
    }
}
