<?php

namespace App\Services\Reecadastro;

use App\Http\Resources\Reecadastro\Solicitacao\SolicitacaoResource;
use App\Models\SolicitacaoReecadastro;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class SolicitacaoReecadastroService
{
    /**
     * Lista as solicitações para a homologação, com filtros de período, situação
     * e busca por nome ou CPF do estudante.
     *
     * @param  array<string, mixed>  $filtros
     */
    public function index(array $filtros = []): JsonResponse|AnonymousResourceCollection
    {
        $solicitacoes = SolicitacaoReecadastro::with(['estudante', 'periodo', 'documentos'])
            ->when($filtros['periodo_id'] ?? null, fn ($query, $periodo) => $query->where('periodo_id', $periodo))
            ->when($filtros['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filtros['busca'] ?? null, fn ($query, $busca) => $query->whereHas(
                'estudante',
                fn ($estudante) => $estudante->where('name', 'like', "%{$busca}%")->orWhere('cpf', 'like', "%{$busca}%"),
            ))
            ->latest('id')
            ->paginate(10);

        if ($solicitacoes->isEmpty()) {
            return response()->json(['message' => 'Nenhuma solicitação de recadastro cadastrada'], 200);
        }

        return SolicitacaoResource::collection($solicitacoes);
    }

    public function show(string $id): JsonResponse
    {
        try {
            $solicitacao = SolicitacaoReecadastro::with(['estudante', 'periodo', 'documentos'])->find($id);

            if (! $solicitacao) {
                return response()->json(['message' => 'Solicitação de recadastro não encontrada'], 404);
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
     * Homologa o recadastro do estudante.
     *
     * Aprovado  -> documentos aceitos e estudante segue ativo.
     * Pendencia -> devolve os documentos informados para reenvio.
     * Rejeitado -> estudante perde o benefício e fica inativo.
     *
     * @param  array<string, mixed>  $data
     */
    public function analise(array $data, string $id): JsonResponse
    {
        try {
            $solicitacao = SolicitacaoReecadastro::with(['estudante', 'documentos'])->find($id);

            if (! $solicitacao) {
                return response()->json(['message' => 'Solicitação de recadastro não encontrada'], 404);
            }

            if ($solicitacao->status === 'Pendente') {
                return response()->json([
                    'message' => 'O estudante ainda não finalizou o envio dos documentos',
                ], 409);
            }

            DB::transaction(function () use ($solicitacao, $data) {
                $decisao = $data['decisao'];

                $solicitacao->update([
                    'status' => $decisao,
                    'observacoes' => $decisao === 'Aprovado' ? null : $data['motivo'],
                    'analisado_por' => Auth::id(),
                    'analisado_em' => now(),
                    'access_token' => null,
                    'token_expira_em' => null,
                ]);

                $devolvidos = $decisao === 'Pendencia' ? ($data['documentos'] ?? []) : [];

                foreach ($solicitacao->documentos as $documento) {
                    $rejeitado = in_array($documento->type, $devolvidos, true);

                    $documento->update([
                        'status' => $rejeitado ? 'Rejeitado' : 'Aprovado',
                        'observacoes' => $rejeitado ? $data['motivo'] : null,
                    ]);
                }

                if ($decisao === 'Aprovado') {
                    $solicitacao->estudante?->update(['status' => 'Ativo']);
                }

                if ($decisao === 'Rejeitado') {
                    $solicitacao->estudante?->update(['status' => 'Inativo']);
                }
            });

            return response()->json([
                'data' => new SolicitacaoResource($solicitacao->refresh()->load(['estudante', 'periodo', 'documentos'])),
                'message' => 'Análise do recadastro registrada com sucesso',
            ], 200);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Erro ao registrar análise do recadastro',
            ], 500);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $solicitacao = SolicitacaoReecadastro::with('documentos')->find($id);

            if (! $solicitacao) {
                return response()->json(['message' => 'Solicitação de recadastro não encontrada'], 404);
            }

            // Os documentos caem por cascata no banco; os arquivos precisam ser
            // removidos do disco explicitamente.
            foreach ($solicitacao->documentos as $documento) {
                if ($documento->file_path) {
                    Storage::delete($documento->file_path);
                }
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
}
