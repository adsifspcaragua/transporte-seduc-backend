<?php

namespace App\Http\Controllers\Api\Estudante;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEstudanteRequest;
use App\Http\Requests\UpdateEstudanteRequest;
use App\Http\Resources\EstudanteResource;
use App\Models\Estudante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EstudanteController extends Controller
{
    
    public function index()
    {
        $estudantes = Estudante::all();

        if($estudantes->isEmpty()) {
            return response()->json(["message" => "Nenhum estudante cadastrado"], 200);
        }
       return EstudanteResource::collection($estudantes);
    }

    
    public function store(StoreEstudanteRequest $request)
    {
        DB::beginTransaction();
        try{
            $estudante = Estudante::create($request->validated());
            DB::commit();
           return response()->json([
            'data' => new EstudanteResource($estudante),
            'message' => 'Estudante criado com sucesso'
            ]);
        }catch(\Exception $e) {
            DB::rollBack();
            return response()->json(
                ["message" => "Erro ao cadastrar estudante",
                       "error" => $e->getMessage()], 500);
        }
    }


    public function show(string $id)
    {
        try{
            $estudante = Estudante::find($id);
            if(is_null($estudante)) {
                return response()->json(["message" => "Estudante não encontrado"], 404);
            }
            return new EstudanteResource([
                "estudante" => $estudante,
                "message" => "Estudante encontrado com sucesso"
            ]);
        }catch(\Exception $e) {
            return response()->json(
                ["message" => "Erro ao encontrar estudante",
                       "error" => $e->getMessage()], 500);
        }
    }

   
    public function update(UpdateEstudanteRequest $request, string $id)
    {
        DB::beginTransaction();
        try{
            $estudante = Estudante::find($id);
            $estudante->update($request->validated());
            DB::commit();
            return response()->json([
                'data' => new EstudanteResource($estudante),
                'message' => 'Estudante atualizado com sucesso'
            ]);

        }catch(\Exception $e) {
            DB::rollBack();
            return response()->json(
                ["message" => "Erro ao atualizar estudante",
                       "error" => $e->getMessage()], 500);
        }
    }


    public function destroy(string $id)
    {

        try{
            $estudante = Estudante::find($id);
            $estudante_exibir = $estudante;
            $estudante->delete();
            return response()->json([
                'data' => new EstudanteResource( $estudante_exibir),
                'message' => 'Estudante deletado com sucesso'
            ]);
        }catch(\Exception $e) {
            return response()->json(
                ["message" => "Erro ao excluir estudante",
                       "error" => $e->getMessage()], 500);
        }
    }
}
