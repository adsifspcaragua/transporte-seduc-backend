<?php

namespace App\Services\User;

use App\Http\Resources\User\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Throwable;

class UserService
{
    public function index(): JsonResponse
    {
        try {
            $users = User::all();

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
            $user = new User;
            $user->fill($data);
            $user->password = Hash::make($data['password']);
            $user->save();

            return response()->json(new UserResource($user), 201);
        } catch (Throwable) {
            return response()->json([
                'message' => 'Falha ao cadastrar usuário',
            ], 500);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $user = User::find($id);

            if (! $user) {
                return response()->json(['message' => 'Usuário não encontrada'], 404);
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
            $user->update($data);

            return response()->json([
                'data' => new UserResource($user),
                'message' => 'Usuário atualizada com sucesso',
            ], 200);
        } catch (Throwable $ex) {
            return response()->json([
                'message' => 'Falha ao atualizar usuário',
                'error' => $ex->getMessage(),
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
}
