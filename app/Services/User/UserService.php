<?php

namespace App\Services\User;

use App\Http\Resources\User\UserResource;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Throwable;

class UserService
{
    public function index(): JsonResponse
    {
        try {
            $users = User::with('roles')->get();

            if ($users->isEmpty()) {
                return response()->json(['message' => 'Nenhum usuário encontrado'], 404);
            }

            return response()->json([
                'data' => UserResource::collection($users),
                'message' => 'Usuários inscritos',
            ], 200);
        } catch (Throwable) {
            return response()->json([
                'message' => 'Nenhum usuário encontrado',
            ], 404);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function store(array $data): JsonResponse
    {
        try {
            $role = $data['role'] ?? null;

            $user = new User;
            $user->fill($data);
            $user->password = Hash::make($data['password']);
            $user->ativo = $data['ativo'] ?? true;
            $user->save();

            $this->syncRole($user, $role);

            return response()->json(new UserResource($user->load('roles')), 201);
        } catch (Throwable $ex) {
            report($ex);

            return response()->json([
                'message' => 'Falha ao cadastrar usuário',
            ], 500);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $user = User::with('roles')->find($id);

            if (! $user) {
                return response()->json(['message' => 'Usuário não encontrada'], 404);
            }

            return response()->json([
                'data' => new UserResource($user),
                'message' => 'Usuário encontrado',
            ], 200);
        } catch (Throwable) {
            return response()->json([
                'message' => 'Usuário não encontrado',
            ], 404);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(array $data, User $user): JsonResponse
    {
        try {
            $role = $data['role'] ?? null;
            unset($data['role']);

            if (array_key_exists('password', $data)) {
                $data['password'] = Hash::make($data['password']);
            }

            $user->update($data);

            if ($role !== null) {
                $this->syncRole($user, $role);
            }

            return response()->json([
                'data' => new UserResource($user->load('roles')),
                'message' => 'Usuário atualizado com sucesso',
            ], 200);
        } catch (Throwable $ex) {
            report($ex);

            return response()->json([
                'message' => 'Falha ao atualizar usuário',
            ], 500);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $user = User::find($id);

            if (! $user) {
                return response()->json([
                    'message' => 'Usuário não encontrado',
                ], 404);
            }

            $user->delete();

            return response()->json([
                'message' => 'Usuário removido com sucesso',
            ], 200);
        } catch (Throwable) {
            return response()->json([
                'message' => 'Falha ao remover usuário',
            ], 500);
        }
    }

    public function inativar(User $user): JsonResponse
    {
        return $this->setAtivo($user, false, 'Usuário inativado com sucesso');
    }

    public function ativar(User $user): JsonResponse
    {
        return $this->setAtivo($user, true, 'Usuário ativado com sucesso');
    }

    private function setAtivo(User $user, bool $ativo, string $message): JsonResponse
    {
        try {
            $user->update(['ativo' => $ativo]);

            return response()->json([
                'data' => new UserResource($user->load('roles')),
                'message' => $message,
            ], 200);
        } catch (Throwable $ex) {
            report($ex);

            return response()->json([
                'message' => 'Falha ao alterar status do usuário',
            ], 500);
        }
    }

    private function syncRole(User $user, ?string $role): void
    {
        if ($role === null) {
            return;
        }

        $user->roles()->sync(Role::where('title', $role)->pluck('id'));
    }
}
