<?php

namespace App\Services\Role;

use App\Http\Resources\Role\RoleResource;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Throwable;

class RoleService
{
    public function index(): JsonResponse|AnonymousResourceCollection
    {
        $roles = Role::all();

        if ($roles->isEmpty()) {
            return response()->json(['message' => 'Nenhum cargo cadastrado'], 200);
        }

        return RoleResource::collection($roles);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function store(array $data): JsonResponse
    {
        try {
            $role = DB::transaction(fn () => Role::create($data));

            return response()->json([
                'data' => new RoleResource($role),
                'message' => 'Cargo criado com sucesso',
            ], 201);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Falha ao criar cargo',
                'erro' => $e->getMessage(),
            ], 401);
        }
    }

    public function show(string $id): JsonResponse|RoleResource
    {
        try {
            $role = Role::find($id);

            if (! $role) {
                return response()->json(['message' => 'Cargo nao encontrado'], 404);
            }

            return new RoleResource($role);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Falha ao buscar cargo',
                'erro' => $e->getMessage(),
            ], 401);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(array $data, string $id): JsonResponse
    {
        try {
            $role = DB::transaction(function () use ($data, $id) {
                $role = Role::find($id);

                if (! $role) {
                    return null;
                }

                $role->update($data);

                return $role;
            });

            if (! $role) {
                return response()->json(['message' => 'Cargo nao encontrado'], 404);
            }

            return response()->json([
                'data' => new RoleResource($role),
                'message' => 'Cargo atualizado com sucesso',
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Falha ao atualizar cargo',
                'erro' => $e->getMessage(),
            ], 401);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $role = Role::find($id);

            if (! $role) {
                return response()->json(['message' => 'Cargo nao encontrado'], 404);
            }

            $roleExibir = $role;
            $role->delete();

            return response()->json([
                'data' => new RoleResource($roleExibir),
                'message' => 'Cargo deletado com sucesso',
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Falha ao deletar cargo',
                'erro' => $e->getMessage(),
            ], 401);
        }
    }
}
