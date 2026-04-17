<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\{StoreInstituicaoRequest, UpdateInstituicaoRequest};
use App\Http\Resources\InstituicaoResource;
use App\Models\Instituicao;

class InstituicaoController extends Controller
{
    
    public function index()
    {
        $instituicoes = Instituicao::all();
        
        if($instituicoes->isEmpty()) {
            return response()->json(["message" => "Nenhuma instituicao cadastrada"], 200);
        }
        return response()->json([
                "instituicao" => InstituicaoResource::collection($instituicoes),
                "message" => "Instituicao encontrada com sucesso"
            ],200);
    }

    
    public function store(StoreInstituicaoRequest $request)
    {
        try{
            $instituicao = Instituicao::create($request->validated());
           return response()->json([
            'data' => new InstituicaoResource($instituicao),
            'message' => 'Instituição criada com sucesso'
            ]);
        }catch(\Exception $e) {
            return response()->json(
                ["message" => "Erro ao cadastrar instituicao",
                       "error" => $e->getMessage()], 500);
        }
    }


    public function show(string $id)
    {
        try{
            $instituicao = Instituicao::find($id);
            if(is_null($instituicao)) {
                return response()->json(["message" => "Instituição não encontrada"], 404);
            }
            return response()->json([
                "instituicao" => new InstituicaoResource($instituicao),
                "message" => "Instituicao encontrada com sucesso"
            ],200);
        }catch(\Exception $e) {
            return response()->json(
                ["message" => "Erro ao encontrar instituicao",
                       "error" => $e->getMessage()], 500);
        }
    }

   
    public function update(UpdateInstituicaoRequest $request, string $id)
    {
        try{

            $instituicao = Instituicao::find($id);
            $instituicao->update($request->validated());
            return response()->json([
                'data' => new InstituicaoResource($instituicao),
                'message' => 'Instituicao atualizada com sucesso'
            ],200);

        }catch(\Exception $e) {
            return response()->json(
                ["message" => "Erro ao atualizar instituicao",
                       "error" => $e->getMessage()], 500);
        }
    }


    public function destroy(string $id)
    {

        try{
            $instituicao = Instituicao::find($id);
            
            if(is_null($instituicao)){
                return response()->json([
                'message' => 'Instituicao não encontrada'
            ]);
            }
            $instituicao_exibir = $instituicao;
            $instituicao->delete();
            return response()->json([
                'data' => new InstituicaoResource( $instituicao_exibir),
                'message' => 'Instituicao deletado com sucesso'
            ]);
        }catch(\Exception $e) {
            return response()->json(
                ["message" => "Erro ao excluir instituicao",
                       "error" => $e->getMessage()], 500);
        }
    }
}
