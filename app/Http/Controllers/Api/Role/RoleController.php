<?php

namespace App\Http\Controllers\Api\Role;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Http\Resources\RoleResource;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $role = Role::all();

        if($role->isEmpty()){
            return response()->json(["message" =>"Nenhum cargo cadastrado"], 200);
        }
        return RoleResource::collection($role);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRoleRequest $request)
    {


        DB::beginTransaction();
        try{

            $role = Role::create($request->validated());

            DB::commit();
           return response()->json([
                'data' => new RoleResource($role),
                'message' => 'Cargo criado com sucesso'
            ]);

        }catch(\Exception $e){
            DB::rollBack();
            return response()->json(["message" =>"Falha ao criar cargo", "erro" => $e], 401);
        }

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try{
            $role = Role::findOrFail($id);

            if(is_null($role)){
                return response()->json(["message" =>"Cargo nao encontrado"], 404);
            }
            return new RoleResource($role);
        }catch(\Exception $e){
            return response()->json(["message" =>"Falha ao buscar cargo", "erro" => $e], 401);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRoleRequest $request, string $id)
    {
        DB::beginTransaction();
          try{
            $role = Role::findOrFail($id);

            $role->update($request->validated());

            DB::commit();

           return response()->json([
                'data' => new RoleResource($role),
                'message' => 'Cargo atualizado com sucesso'
            ]);
        }catch(\Exception $e){
            DB::rollBack();
            return response()->json(["message" =>"Falha ao atualizar cargo", "erro" => $e], 401);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try{

            $role = Role::findOrFail($id);

            $role_exibir = $role;

            $role->delete();

            return response()->json([
                'data' => new RoleResource($role_exibir),
                'message' => 'Cargo deletado com sucesso'
            ]);
        }catch(\Exception $e){
            return response()->json(["message" =>"Falha ao deletar cargo", "erro" => $e->getMessage()], 401);
        }
    }
}
