<?php

namespace App\Http\Controllers\Api\User;

use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use App\Http\Requests\{StoreUserRequest, UpdateUserRequest};
use App\Models\User;
use App\Http\Resources\UserResource;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $users = User::all();

            if(is_null($users)) {
                return response()->json(["message" => "Nenhum usuário encontrado"], 404);
            }

            return response()->json(
                [
                    "data" => UserResource::collection($users),
                    "message"=> "Usuários inscritos"
                ], 200);

        } catch (\Exception $ex) {
            return response()->json([
                'message' => 'Nenhum usuário encontrado'
            ], 404);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        $data = $request->validated();

        try{
            $user = new User();
            $user->fill($data);
            $user->password = Hash::make($data['password']);
            $user->save();

            return response()->json(new UserResource($user),201);
        }catch(\Exception $ex){
            return response()->json([
                'message' => 'Falha ao cadastrar usuário'
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $user = User::find($id);
            if(is_null($user)) {
                return response()->json(["message" => "Usuário não encontrada"], 404);
            }
            return response()->json(
                [
                    "data" => new UserResource($user),
                    "message"=> "Usuário encontrado"
                ], 200);

        } catch (\Exception $ex) {
            return response()->json([
                'message' => 'Usuário não encontrado',
                
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        try {
            $user->update($request->validated());
            return response()->json([
                "data" => new UserResource($user),
                'message' => 'Usuário atualizada com sucesso'],200);

        } catch (\Exception $ex) {
            return response()->json([
                'message' => 'Falha ao atualizar usuário',
                'error' => $ex->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $user = User::find($id);
            if(!$user){
                return response()->json([
                    'message' => 'Usuário não encontrado'
                ], 404);
            }
            $user->delete();

            return response()->json([
                'message' => 'Usuário removido com sucesso'
            ], 200);

        } catch (\Exception $ex) {
            return response()->json([
                'message' => 'Falha ao remover usuário'
            ], 500);
        }
    }
}
