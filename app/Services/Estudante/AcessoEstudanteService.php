<?php

namespace App\Services\Estudante;

use App\Http\Resources\Inscricao\Documento\DocumentoResource;
use App\Http\Resources\Inscricao\InscricaoResource;
use App\Http\Resources\Inscricao\Instituicao\InscricaoInstituicaoResource;
use App\Http\Resources\Reecadastro\Publico\SituacaoResource;
use App\Models\Estudante;
use App\Models\Inscricao;
use App\Models\PeriodoReecadastro;
use App\Models\SolicitacaoReecadastro;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class AcessoEstudanteService
{
    public function __construct(private readonly CodigoAcessoService $codigos) {}

    /**
     * Primeira etapa: CPF novo segue para a inscricao; com cadastro, so o envio
     * do codigo, sem nenhum dado da pessoa.
     */
    public function solicitar(string $cpf): JsonResponse
    {
        if (Estudante::where('cpf', $cpf)->exists() || Inscricao::where('cpf', $cpf)->exists()) {
            return $this->codigos->solicitar($cpf);
        }

        return response()->json([
            'fluxo' => 'inscricao',
            'data' => ['cpf' => $cpf],
        ]);
    }

    public function verificar(string $cpf, string $codigo): JsonResponse
    {
        return $this->codigos->confirmar($cpf, $codigo)
            ? $this->acessar($cpf)
            : $this->codigos->recusa();
    }

    private function acessar(string $cpf): JsonResponse
    {
        $estudante = Estudante::with(['inscricao.inscricao_instituicao'])->where('cpf', $cpf)->first();

        if ($estudante) {
            return $this->recadastro($estudante);
        }

        $inscricao = Inscricao::with(['inscricao_instituicao', 'inscricao_documentos'])
            ->where('cpf', $cpf)
            ->first();

        if (! $inscricao) {
            return response()->json([
                'fluxo' => 'inscricao',
                'data' => ['cpf' => $cpf],
            ]);
        }

        if (in_array($inscricao->status, ['Aprovado', 'Rejeitado'], true)) {
            return response()->json([
                'message' => 'Esta inscrição já foi analisada. Procure a responsável pelo transporte universitário.',
            ], 409);
        }

        $inscricao->update(['access_token' => Str::random(64)]);

        return response()->json([
            'fluxo' => 'lista_espera',
            'data' => [
                'pode_editar' => in_array($inscricao->status, ['Incompleto', 'Em revisao'], true),
                'inscricao' => new InscricaoResource($inscricao->refresh()),
                'instituicao' => $inscricao->inscricao_instituicao
                    ? new InscricaoInstituicaoResource($inscricao->inscricao_instituicao)
                    : null,
                'documentos' => DocumentoResource::collection($inscricao->inscricao_documentos),
            ],
        ]);
    }

    private function recadastro(Estudante $estudante): JsonResponse
    {
        if ($estudante->situacao !== 'Ativo') {
            return response()->json([
                'message' => 'Seu cadastro não está ativo. Procure a responsável pelo transporte universitário.',
            ], 403);
        }

        $periodo = PeriodoReecadastro::aberto();

        if (! $periodo) {
            return response()->json(['message' => 'O período de recadastro está fechado no momento.'], 409);
        }

        $solicitacao = SolicitacaoReecadastro::firstOrCreate(
            ['estudante_id' => $estudante->id, 'periodo_id' => $periodo->id],
            ['status' => 'Pendente'],
        );
        $solicitacao->update([
            'access_token' => Str::random(64),
            'token_expira_em' => now()->addHours(2),
        ]);
        $solicitacao->load(['documentos', 'estudante.inscricao.inscricao_instituicao', 'periodo']);

        return response()->json([
            'fluxo' => 'recadastro',
            'data' => new SituacaoResource($solicitacao),
        ]);
    }
}
