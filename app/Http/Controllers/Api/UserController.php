<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use App\Http\Requests\{StoreUserRequest, UpdateUserRequest};
use App\Models\User;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $users = User::paginate(10);
            return response()->json($users, 200);

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

            return response()->json($user, 201);
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
            $user = User::findOrFail($id);
            return response()->json($user, 200);

        } catch (\Exception $ex) {
            return response()->json([
                'message' => 'Usuário não encontrado'
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        try {
            $user->update($request->validated()['user']);
            return response()->json($user, 200);

        } catch (\Exception $ex) {
            return response()->json([
                'message' => 'Falha ao atualizar usuário'
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
