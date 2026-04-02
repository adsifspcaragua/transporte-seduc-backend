<?php

namespace App\Http\Controllers\Api\Estudante;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inscricao\Instituicao\{StoreInscricaoIntituicoesRequest, UpdateInscricaoIntituicoesRequest};
use App\Models\InscricaoInstituicoes;
use App\Http\Resources\Inscricao\InscricaoInstituicaoResource;
class InscricaoInstituicaoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
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
    public function create(StoreInscricaoIntituicoesRequest $request)
    {
        try {
            $inscricao = InscricaoInstituicoes::create($request->validated());

            return response()->json([
                "data" => new InscricaoInstituicaoResource($inscricao),
                "message" => "Inscrição criada com sucesso"
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao criar inscrição.'], 500);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
         try {
            $inscricao_instituicao = InscricaoInstituicoes::find($id);

            if(is_null($inscricao_instituicao)) {
                return response()->json(["message" => "Inscricao não encontrada"], 404);
            }

            return new InscricaoInstituicaoResource([
                "data" => new InscricaoInstituicaoResource($inscricao_instituicao),
                "message" => "Incricao encontrado com sucesso"
            ],200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao buscar inscrição.'], 500);
        }
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateInscricaoIntituicoesRequest $request, InscricaoInstituicoes  $inscricao_instituicao)
    {
        try {
            $inscricao_instituicao->update($request->validated());

            return response()->json([
                "data" => new InscricaoInstituicaoResource($inscricao_instituicao),
                'message' => 'Inscricao atualizada com sucesso'],200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao atualizar inscrição.'], 500);
        }

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }



}
