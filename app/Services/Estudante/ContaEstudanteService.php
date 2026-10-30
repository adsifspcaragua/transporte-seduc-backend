<?php

namespace App\Services\Estudante;

use App\Models\Estudante;
use App\Models\Role;
use App\Models\User;
use App\Services\Auditoria\AuditoriaService;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Throwable;

/**
 * Cria o login do estudante, ligado ao cadastro dele.
 *
 * E uma rota propria porque POST /api/users so cria contas da equipe. A senha
 * inicial e aleatoria e ninguem a conhece: o estudante define a sua pelo link
 * enviado ao e-mail do cadastro. Se o link vencer, vale o "esqueci a senha".
 */
class ContaEstudanteService
{
    public function __construct(private readonly AuditoriaService $auditoria) {}

    public function criar(User $responsavel, Estudante $estudante): JsonResponse
    {
        try {
            if ($estudante->user_id !== null) {
                return response()->json(['message' => 'Este estudante já tem uma conta.'], 409);
            }

            if ($estudante->situacao !== 'Ativo') {
                return response()->json(['message' => 'Só estudantes com o benefício ativo podem receber uma conta.'], 422);
            }

            // Conta existente nao e aproveitada: ligar um estudante a conta de
            // outra pessoa daria a ela o acesso dele, e vice-versa.
            $emUso = User::where('email', $estudante->email)
                ->orWhereRaw("REPLACE(REPLACE(cpf, '.', ''), '-', '') = ?", [preg_replace('/\D/', '', $estudante->cpf)])
                ->exists();

            if ($emUso) {
                return response()->json(['message' => 'Já existe uma conta com o e-mail ou o CPF deste estudante.'], 409);
            }

            $user = DB::transaction(function () use ($estudante) {
                $user = User::create([
                    'name' => $estudante->name,
                    'email' => $estudante->email,
                    'cpf' => $estudante->cpf,
                    'data_nascimento' => $estudante->birth_date,
                    'password' => Str::random(40),
                    'ativo' => true,
                ]);

                $user->roles()->sync(Role::where('title', 'estudante')->pluck('id'));
                $estudante->update(['user_id' => $user->id]);

                return $user;
            });

            $this->auditoria->registrar($responsavel, 'estudante.conta.criar', $estudante, ['user_id' => $user->id]);

            return response()->json([
                'data' => [
                    'user_id' => $user->id,
                    'estudante_id' => $estudante->id,
                    'convite_enviado' => $this->enviarConvite($user),
                ],
                'message' => 'Conta criada. O estudante define a senha pelo link enviado ao e-mail do cadastro.',
            ], 201);
        } catch (UniqueConstraintViolationException) {
            return response()->json(['message' => 'Este estudante já tem uma conta.'], 409);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['message' => 'Erro ao criar a conta do estudante'], 500);
        }
    }

    /**
     * Falha de e-mail nao desfaz a conta: o estudante ainda pode pedir o link
     * pelo "esqueci a senha".
     */
    private function enviarConvite(User $user): bool
    {
        try {
            return Password::sendResetLink(['email' => $user->email]) === Password::ResetLinkSent;
        } catch (Throwable $e) {
            report($e);

            return false;
        }
    }
}
