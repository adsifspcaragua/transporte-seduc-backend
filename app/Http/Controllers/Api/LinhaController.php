<?php

namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use App\Models\Linha;
use App\Http\Resources\LinhaResource;
use App\Http\Requests\{StoreLinhaRequest, UpdateLinhaRequest};

class LinhaController extends Controller
{
    
    public function index()
    {
        try{
            $linhas = Linha::all();
            if($linhas->isEmpty()) {
                return response()->json(["message" => "Nenhuma linha cadastrada"], 200);
            }
            return response()->json([
                'data' =>  LinhaResource::collection($linhas),
                'message' => 'Instituição criada com sucesso'
            ]);
        }
        catch(\Exception $e){
            return response()->json(
                ["message" => "Erro ao encontrar linhas",
                       "error" => $e->getMessage()], 500);
        }
    }

    
    public function store(StoreLinhaRequest $request)
    {
        try{
            $linha = Linha::create([...$request->validated(), 'used_capacity' => 0]);
            return response()->json([
                'data' => new LinhaResource($linha),
                'message' => 'Linha criada com sucesso'
            ], 200);
        }catch(\Exception $e) {
            return response()->json(
                ["message" => "Erro ao cadastrar linha",
                       "error" => $e->getMessage()], 500);
        }
    }


    public function show(string $id)
    {
        try{
            $linha = Linha::find($id);
            if(is_null($linha)) {
                return response()->json(["message" => "Linha não encontrada"], 404);
            }
            return response()->json([
                "data" => new LinhaResource($linha),
                "message" => "Linha encontrada com sucesso"
            ],200);
        }catch(\Exception $e) {
            return response()->json(
                ["message" => "Erro ao encontrar linha",
                       "error" => $e->getMessage()], 500);
        }
    }

   
    public function update(UpdateLinhaRequest $request, string $id)
    {
        try{
            $linha = Linha::find($id);
            $linha->update($request->validated());
            return response()->json([
                'data' => new LinhaResource($linha),
                'message' => 'Linha atualizada com sucesso'
            ],200);
            

        }catch(\Exception $e) {
            return response()->json(
                ["message" => "Erro ao atualizar linha",
                       "error" => $e->getMessage()], 500);
        }
    }


    public function destroy(string $id)
    {

        try{
            $linha = Linha::find($id);
            
            if(is_null($linha)){
                return response()->json([
                'message' => 'Linha não encontrada'
            ]);
            }
            $linha_exibir = $linha;
            $linha->delete();
            return response()->json([
                'data' => new LinhaResource( $linha_exibir),
                'message' => 'Linha deletada com sucesso'
            ]);
        }catch(\Exception $e) {
            return response()->json(
                ["message" => "Erro ao excluir linha",
                       "error" => $e->getMessage()], 500);
        }
    }
}