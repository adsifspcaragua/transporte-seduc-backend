<?php

namespace App\Http\Controllers\Api\Estudante;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inscricao\Instituicao\{StoreInscricaoIntituicoesRequest, UpdateInscricaoIntituicoesRequest};
use App\Models\InscricaoInstituicoes;
use App\Http\Resources\Inscricao\InscricaoInstituicaoResource;
class InscricaoInstituicaoController extends Controller
{
    
    public function index()
    {
        $inscricoes_instituicao = InscricaoInstituicoes::all();

        if($inscricoes_instituicao->isEmpty()) {
            return response()->json(["message" => "Nenhuma inscricao cadastrada"], 200);
        }
       return InscricaoInstituicaoResource::collection($inscricoes_instituicao);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function store(StoreInscricaoIntituicoesRequest $request)
    {
        
        try {
            $data = $request->validated();
            $inscricao = InscricaoInstituicoes::create([...$data, 'line_id'=> 0]);

            return response()->json([
                "data" => new InscricaoInstituicaoResource($inscricao),
                "message" => "Inscrição criada com sucesso"
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao criar inscrição.', 'error' => $e->getMessage()], 500);
        }
    }



    public function show(string $id, string $instituicao)
    {
         try {
           

            $inscricao_instituicao = InscricaoInstituicoes::find($instituicao);

            if(is_null($inscricao_instituicao)) {
                return response()->json(["message" => "Inscricao não encontrada"], 404);
            }

            return response()->json([
                "data" => new InscricaoInstituicaoResource($inscricao_instituicao),
                "message" => "Incricao encontrado com sucesso"
            ],200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao buscar inscrição.'], 500);
        }
    }


   
    public function update(UpdateInscricaoIntituicoesRequest $request, string $inscricao, string $instituicao)
    {
        try {

            $inscricao_instituicao = InscricaoInstituicoes::find($instituicao);
            $data = $request->validated();

            if(is_null($inscricao_instituicao)){
                return response()->json([
                'message' => 'Inscricao não encontrada'],404);
            }
            
            $inscricao_instituicao->update($data);

            return response()->json([
                "data" => new InscricaoInstituicaoResource($inscricao_instituicao),
                'message' => 'Inscricao atualizada com sucesso'],200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao atualizar inscrição.'], 500);
        }

    }


    public function destroy(string $id)
    {
        //
    }



}
