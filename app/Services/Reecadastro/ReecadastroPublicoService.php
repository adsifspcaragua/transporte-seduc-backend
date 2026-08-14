<?php

namespace App\Services\Reecadastro;

use App\Http\Requests\Reecadastro\Publico\EnviarDocumentoRequest;
use App\Http\Resources\Reecadastro\Publico\SituacaoResource;
use App\Models\DocumentacaoReecadastro;
use App\Models\Estudante;
use App\Models\PeriodoReecadastro;
use App\Models\SolicitacaoReecadastro;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

/**
 * Fluxo do estudante, sem login: ele informa o CPF e, se já estiver no sistema
 * e o recadastro estiver aberto, envia os documentos do período.
 *
 * A consulta devolve um token de curta duração que autoriza os envios
 * seguintes, para que o ID da solicitação sozinho não dê acesso aos dados.
 */
class ReecadastroPublicoService
{
    /** Validade do token da sessão pública, em horas. */
    private const TOKEN_HORAS = 2;

    /**
     * Consulta a situação do estudante pelo CPF e abre a solicitação do período
     * corrente quando ainda não existir.
     */
    public function consulta(string $cpf): JsonResponse
    {
        try {
            $periodo = PeriodoReecadastro::aberto();

            if (! $periodo) {
                return response()->json([
                    'message' => 'O período de recadastro está fechado no momento.',
                ], 409);
            }

            $estudante = Estudante::where('cpf', $cpf)->first();

            if (! $estudante) {
                return response()->json([
                    'message' => 'CPF não encontrado. Somente estudantes já cadastrados no transporte podem recadastrar.',
                ], 404);
            }

            if ($estudante->status !== 'Ativo') {
                return response()->json([
                    'message' => 'Seu cadastro não está ativo. Procure a responsável pelo transporte universitário.',
                ], 403);
            }

            $solicitacao = SolicitacaoReecadastro::firstOrCreate(
                ['estudante_id' => $estudante->id, 'periodo_id' => $periodo->id],
                ['status' => 'Pendente'],
            );

            $solicitacao->update([
                'access_token' => Str::random(64),
                'token_expira_em' => now()->addHours(self::TOKEN_HORAS),
            ]);

            $solicitacao->load(['documentos', 'estudante', 'periodo']);

            return response()->json([
                'data' => new SituacaoResource($solicitacao),
                'message' => $this->mensagemSituacao($solicitacao),
            ], 200);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Erro ao consultar o recadastro',
            ], 500);
        }
    }

    /**
     * Envia (ou reenvia) um dos documentos exigidos no período.
     */
    public function enviarDocumento(EnviarDocumentoRequest $request, string $id): JsonResponse
    {
        try {
            $data = $request->validated();
            $solicitacao = SolicitacaoReecadastro::with(['documentos', 'estudante', 'periodo'])->find($id);

            if ($erro = $this->recusarEnvio($solicitacao, $data['token'])) {
                return $erro;
            }

            if ($solicitacao->status === 'Pendencia' && ! $this->pendente($solicitacao, $data['type'])) {
                return response()->json([
                    'message' => 'Este documento já foi aceito. Reenvie apenas os documentos devolvidos pela análise.',
                ], 403);
            }

            $arquivo = $request->file('arquivo');
            $pasta = 'reecadastro/'.$solicitacao->periodo->ano.'-'.$solicitacao->periodo->semestre.'/'.$solicitacao->estudante_id;
            $caminho = $arquivo->storeAs($pasta, $data['type'].'.'.$arquivo->getClientOriginalExtension());

            $anterior = $solicitacao->documentos->firstWhere('type', $data['type']);

            if ($anterior && $anterior->file_path && $anterior->file_path !== $caminho) {
                Storage::delete($anterior->file_path);
            }

            DocumentacaoReecadastro::updateOrCreate(
                ['solicitacao_id' => $solicitacao->id, 'type' => $data['type']],
                [
                    'estudante_id' => $solicitacao->estudante_id,
                    'file_path' => $caminho,
                    'nome_original' => $arquivo->getClientOriginalName(),
                    'status' => 'Enviado',
                    'observacoes' => null,
                ],
            );

            $solicitacao->load('documentos');

            return response()->json([
                'data' => new SituacaoResource($solicitacao),
                'message' => 'Documento enviado com sucesso.',
            ], 200);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Erro ao enviar documento de recadastro',
            ], 500);
        }
    }

    /**
     * Encerra o envio: valida os documentos exigidos (considerando os pedidos
     * de prazo adicional) e coloca a solicitação em análise.
     *
     * @param  array<string, mixed>  $data
     */
    public function finalizar(array $data, string $id): JsonResponse
    {
        try {
            $solicitacao = SolicitacaoReecadastro::with(['documentos', 'estudante', 'periodo'])->find($id);

            if ($erro = $this->recusarEnvio($solicitacao, $data['token'])) {
                return $erro;
            }

            $prazos = [
                'declaracao_matricula' => ! $data['possui_matricula'],
                'cronograma_aulas' => ! $data['possui_cronograma'],
            ];

            $faltando = [];

            foreach (DocumentacaoReecadastro::TIPOS as $tipo => $label) {
                if ($prazos[$tipo] ?? false) {
                    continue;
                }

                $documento = $solicitacao->documentos->firstWhere('type', $tipo);

                if (! $documento || $documento->status === 'Rejeitado') {
                    $faltando[] = $label;
                }
            }

            if ($faltando !== []) {
                return response()->json([
                    'message' => 'Envie todos os documentos antes de finalizar. Falta: '.implode(', ', $faltando),
                ], 422);
            }

            $solicitacao->update([
                'status' => 'Em analise',
                'prazo_matricula' => $prazos['declaracao_matricula'],
                'prazo_cronograma' => $prazos['cronograma_aulas'],
                'aceite_veracidade' => true,
                'aceite_ciencia' => true,
                'enviada_em' => now(),
                'observacoes' => null,
                'access_token' => null,
                'token_expira_em' => null,
            ]);

            return response()->json([
                'data' => new SituacaoResource($solicitacao->refresh()->load('documentos')),
                'message' => 'Recadastro enviado. Seus dados estão em análise.',
            ], 200);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Erro ao finalizar o recadastro',
            ], 500);
        }
    }

    /**
     * Verifica solicitação, token e situação antes de aceitar qualquer envio.
     * Devolve a resposta de erro correspondente ou null quando está tudo certo.
     */
    private function recusarEnvio(?SolicitacaoReecadastro $solicitacao, string $token): ?JsonResponse
    {
        if (! $solicitacao) {
            return response()->json(['message' => 'Solicitação de recadastro não encontrada'], 404);
        }

        if (! $solicitacao->access_token
            || ! hash_equals($solicitacao->access_token, $token)
            || ! $solicitacao->token_expira_em
            || $solicitacao->token_expira_em->isPast()
        ) {
            return response()->json([
                'message' => 'Sessão expirada. Informe o CPF novamente para continuar.',
            ], 401);
        }

        if ($solicitacao->periodo->status !== 'Aberto') {
            return response()->json([
                'message' => 'O período de recadastro está fechado no momento.',
            ], 409);
        }

        if (! $solicitacao->aceitaEnvio()) {
            return response()->json([
                'message' => $solicitacao->finalizada()
                    ? 'Este recadastro já foi analisado.'
                    : 'Seus dados já foram enviados e estão em análise.',
            ], 409);
        }

        return null;
    }

    /** O documento foi devolvido pela análise (ou nunca foi enviado). */
    private function pendente(SolicitacaoReecadastro $solicitacao, string $tipo): bool
    {
        $documento = $solicitacao->documentos->firstWhere('type', $tipo);

        return ! $documento || $documento->status === 'Rejeitado';
    }

    private function mensagemSituacao(SolicitacaoReecadastro $solicitacao): string
    {
        return match ($solicitacao->status) {
            'Em analise' => 'Seus dados já foram enviados e estão em análise.',
            'Pendencia' => 'A análise devolveu documentos para reenvio.',
            'Aprovado' => 'Seu recadastro foi aprovado.',
            'Rejeitado' => 'Seu recadastro foi rejeitado.',
            default => 'Recadastro liberado para o envio dos documentos.',
        };
    }
}
