<?php

namespace App\Services\Estudante;

use App\Models\CodigoAcesso;
use App\Models\Estudante;
use App\Models\Inscricao;
use App\Models\User;
use App\Services\Auditoria\AuditoriaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * O atendimento corrige o e-mail de quem perdeu o acesso ao endereco cadastrado.
 *
 * Esse e-mail e o que libera o acesso por CPF e o link de senha: trocar e dar
 * acesso a conta. Por isso a acao exige justificativa, de como a identidade foi
 * conferida, e fica na auditoria, sem gravar os enderecos completos.
 *
 * Cadastro e inscricao mudam juntos. A conta e os envios antigos sao tratados
 * no proprio modelo Estudante, para valer tambem nas outras formas de alterar o
 * e-mail. Inscricao sem estudante nao tem esse evento, entao o codigo cai aqui.
 */
class CorrecaoEmailService
{
    public function __construct(
        private readonly CodigoAcessoService $codigos,
        private readonly AuditoriaService $auditoria,
    ) {}

    public function corrigir(User $responsavel, ?Estudante $estudante, ?Inscricao $inscricao, string $email, string $justificativa): JsonResponse
    {
        try {
            $alvo = $estudante ?? $inscricao;
            $anterior = (string) $alvo->email;

            if ($anterior === $email) {
                return response()->json([
                    'message' => 'Este já é o e-mail do cadastro.',
                    'errors' => ['email' => ['Este já é o e-mail do cadastro.']],
                ], 422);
            }

            DB::transaction(function () use ($estudante, $inscricao, $email, $alvo) {
                $inscricao?->update(['email' => $email]);
                $estudante?->update(['email' => $email]);

                CodigoAcesso::where('cpf', $alvo->cpf)->delete();
            });

            $this->auditoria->registrar($responsavel, 'email.corrigir', $alvo, [
                'antes' => $anterior !== '' ? $this->codigos->mascarar($anterior) : null,
                'depois' => $this->codigos->mascarar($email),
                'estudante_id' => $estudante?->id,
                'inscricao_id' => $inscricao?->id,
                'user_id' => $estudante?->user_id,
            ], $justificativa);

            return response()->json([
                'data' => [
                    'email' => $email,
                    'estudante_id' => $estudante?->id,
                    'inscricao_id' => $inscricao?->id,
                    'conta_atualizada' => $estudante?->user_id !== null,
                ],
                'message' => 'E-mail corrigido. O estudante já pode pedir um novo código.',
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['message' => 'Erro ao corrigir o e-mail'], 500);
        }
    }
}
