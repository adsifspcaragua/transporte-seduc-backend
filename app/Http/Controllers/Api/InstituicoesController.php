<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\{StoreInstituicoesRequest, UpdateInstituicoesRequest};
use App\Http\Resources\InstituicoesResource;
use App\Models\Instituicoes;

class InstituicoesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $instituicoes = Instituicoes::all();
        
        if($instituicoes->isEmpty()) {
            return response()->json(["message" => "Nenhuma instituicao cadastrada"], 200);
        }
        return response()->json([
                "instituicao" => InstituicoesResource::collection($instituicoes),
                "message" => "Instituicao encontrada com sucesso"
            ],200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreInstituicoesRequest $request)
    {
        try{
            $instituicao = Instituicoes::create($request->validated());
           return response()->json([
            'data' => new InstituicoesResource($instituicao),
            'message' => 'Instituição criada com sucesso'
            ]);
        }catch(\Exception $e) {
            return response()->json(
                ["message" => "Erro ao cadastrar instituicao",
                       "error" => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try{
            $instituicao = Instituicoes::find($id);
            if(is_null($instituicao)) {
                return response()->json(["message" => "Instituição não encontrada"], 404);
            }
            return response()->json([
                "instituicao" => new InstituicoesResource($instituicao),
                "message" => "Instituicao encontrada com sucesso"
            ],200);
        }catch(\Exception $e) {
            return response()->json(
                ["message" => "Erro ao encontrar instituicao",
                       "error" => $e->getMessage()], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateInstituicoesRequest $request, string $id)
    {
        try{
            $instituicao = Instituicoes::find($id);
            $instituicao->update($request->validated());
            return response()->json([
                'data' => new InstituicoesResource($instituicao),
                'message' => 'instituicao atualizada com sucesso'
            ],200);

        }catch(\Exception $e) {
            return response()->json(
                ["message" => "Erro ao atualizar instituicao",
                       "error" => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {

        try{
            $instituicao = Instituicoes::find($id);
            $instituicao_exibir = $instituicao;
            $instituicao->delete();
            return response()->json([
                'data' => new InstituicoesResource( $instituicao_exibir),
                'message' => 'Instituicao deletado com sucesso'
            ]);
        }catch(\Exception $e) {
            return response()->json(
                ["message" => "Erro ao excluir instituicao",
                       "error" => $e->getMessage()], 500);
        }
    }
}
