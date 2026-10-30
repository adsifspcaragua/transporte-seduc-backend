<?php

namespace App\Services\Estudante;

use App\Mail\Acesso\CodigoAcessoMail;
use App\Models\CodigoAcesso;
use App\Models\Estudante;
use App\Models\Inscricao;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * O segundo fator do acesso sem login. CPF nao e segredo: sozinho, ele so dispara
 * o envio de um codigo para o e-mail do cadastro. Os dados e o token de edicao
 * so saem para quem devolve esse codigo.
 */
class CodigoAcessoService
{
    private const VALIDADE_MINUTOS = 10;

    /** Pedir de novo logo em seguida nao gera outro e-mail. */
    private const INTERVALO_SEGUNDOS = 60;

    /** Protege a caixa de entrada do estudante contra quem so sabe o CPF dele. */
    private const MAXIMO_POR_HORA = 5;

    /** Com 6 digitos, poucas tentativas mantem adivinhar impraticavel. */
    private const MAXIMO_TENTATIVAS = 5;

    public function solicitar(string $cpf): JsonResponse
    {
        try {
            $email = Estudante::where('cpf', $cpf)->value('email') ?? Inscricao::where('cpf', $cpf)->value('email');

            if (blank($email)) {
                return response()->json([
                    'message' => 'Este cadastro não tem e-mail. Procure a responsável pelo transporte universitário.',
                ], 409);
            }

            if (! $this->enviadoAgoraHaPouco($cpf)) {
                if (CodigoAcesso::where('cpf', $cpf)->where('created_at', '>=', now()->subHour())->count() >= self::MAXIMO_POR_HORA) {
                    return response()->json([
                        'message' => 'Muitos códigos pedidos para este CPF. Aguarde e tente novamente mais tarde.',
                    ], 429);
                }

                Mail::to($email)->queue(new CodigoAcessoMail($this->gerar($cpf), self::VALIDADE_MINUTOS));
            }

            return response()->json([
                'fluxo' => 'verificacao',
                'data' => [
                    'email_mascarado' => $this->mascarar($email),
                    'validade_minutos' => self::VALIDADE_MINUTOS,
                    'reenvio_em_segundos' => self::INTERVALO_SEGUNDOS,
                ],
                'message' => 'Enviamos um código para o e-mail do seu cadastro.',
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['message' => 'Erro ao enviar o código de acesso'], 500);
        }
    }

    /**
     * Cria um codigo novo, invalidando os anteriores, e devolve-o em claro.
     */
    public function gerar(string $cpf): string
    {
        $codigo = str_pad((string) random_int(0, 999_999), 6, '0', STR_PAD_LEFT);

        CodigoAcesso::where('cpf', $cpf)->whereNull('usado_em')->update(['usado_em' => now()]);

        CodigoAcesso::create([
            'cpf' => $cpf,
            'codigo_hash' => $this->hash($cpf, $codigo),
            'expira_em' => now()->addMinutes(self::VALIDADE_MINUTOS),
        ]);

        return $codigo;
    }

    /**
     * Consome o codigo. Cada erro gasta uma tentativa, e o acerto o invalida.
     */
    public function confirmar(string $cpf, string $codigo): bool
    {
        $registro = CodigoAcesso::where('cpf', $cpf)
            ->whereNull('usado_em')
            ->where('expira_em', '>', now())
            ->latest('id')
            ->first();

        if (! $registro) {
            return false;
        }

        // Condicional no banco: requisicoes simultaneas nao furam o limite.
        $contada = CodigoAcesso::whereKey($registro->id)
            ->where('tentativas', '<', self::MAXIMO_TENTATIVAS)
            ->increment('tentativas');

        if ($contada === 0 || ! hash_equals($registro->codigo_hash, $this->hash($cpf, $codigo))) {
            return false;
        }

        return CodigoAcesso::whereKey($registro->id)->whereNull('usado_em')->update(['usado_em' => now()]) === 1;
    }

    public function recusa(): JsonResponse
    {
        $mensagem = 'Código inválido ou expirado. Confira o e-mail ou peça um novo código.';

        return response()->json([
            'message' => $mensagem,
            'errors' => ['codigo' => [$mensagem]],
        ], 422);
    }

    private function enviadoAgoraHaPouco(string $cpf): bool
    {
        return CodigoAcesso::where('cpf', $cpf)
            ->whereNull('usado_em')
            ->where('expira_em', '>', now())
            ->where('created_at', '>', now()->subSeconds(self::INTERVALO_SEGUNDOS))
            ->exists();
    }

    /**
     * Com 6 digitos, um SHA-256 puro se quebraria por forca bruta quem lesse o
     * banco; o HMAC depende tambem da chave da aplicacao.
     */
    private function hash(string $cpf, string $codigo): string
    {
        return hash_hmac('sha256', "{$cpf}|{$codigo}", (string) config('app.key'));
    }

    /** an***@g***.com: o bastante para o dono reconhecer, pouco para os outros. */
    private function mascarar(string $email): string
    {
        [$usuario, $dominio] = array_pad(explode('@', $email, 2), 2, '');
        $final = str_contains($dominio, '.') ? substr($dominio, strrpos($dominio, '.')) : '';

        return mb_substr($usuario, 0, 2).'***@'.mb_substr($dominio, 0, 1).'***'.$final;
    }
}
