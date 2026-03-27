<?php

namespace App\Http\Controllers\Api\Role;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRoleRequest;
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
        return new RoleResource(Role::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRoleRequest $request)
    {

        $request->validate();
        // DB::beginTransaction();
        try{

            $role = Role::create([
                "title" => $request->title
            ]);

            return new RoleResource($role);

        }catch(\Exception $e){
            return response()->json(["message" =>"Falha ao criar cargo", "erro" => $e], 401);
        }

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try{
            return new RoleResource(Role::findOrFail($id));
        }catch(\Exception $e){
            return response()->json(["message" =>"Falha ao buscar cargo", "erro" => $e], 401);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
          try{
            $role = Role::findOrFail($id);

            $role->update([
                "title" => $request->title
            ]);


            return new RoleResource($role);
        }catch(\Exception $e){
            return response()->json(["message" =>"Falha ao editar cargo", "erro" => $e], 401);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        Role::findOrFail($id)->delete;
    }
}
