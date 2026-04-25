<?php

namespace App\Http\Controllers\Api\Estudante;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inscricao\StoreInscricaoRequest;
use App\Http\Requests\Inscricao\UpdateInscricaoRequest;
use App\Http\Resources\Inscricao\InscricaoResource;
use App\Models\Inscricao;
use App\Services\InscricaoService;



class InscricaoController extends Controller
{
    private $inscricaoService;

    public function __construct(InscricaoService $inscricaoService){

        $this->inscricaoService = $inscricaoService;
    }
    public function index()
    {
        try {
            $inscricao = Inscricao::all();
            if($inscricao->isEmpty()) {
                return response()->json(["message" => "Nenhuma inscricao cadastrada"], 200);
            }
            return InscricaoResource::collection($inscricao);
            

        } catch (\Exception $ex) {
            return response()->json([
                'message' => 'Nenhuma inscrição encontrada'
            ], 404);
        }
    }

    public function store(StoreInscricaoRequest $request)
    {
        
        try{
            $data = $request->validated();
            $inscricao = new Inscricao();
            $inscricao->fill($data);
            $inscricao->save();

            if($this->inscricaoService->isComplete($inscricao)){
                $inscricao->update(["status" => "Em analise"]);
            }else{
                 $inscricao->update(["status" => "Incompleto"]);
            }

            return response()->json(new InscricaoResource($inscricao),201);
        }catch(\Exception $ex){
            return response()->json([
                'message' => 'Falha ao criar inscrição',
                'error' => $ex->getMessage()
            ], 500);
        }
    }
       

    public function show(string $id)
    {

        try {
            $inscricao = Inscricao::find($id);

            if(is_null($inscricao)) {
                return response()->json(["message" => "Inscricao não encontrada"], 404);
            }
            
            return response()->json([
                "data" => new InscricaoResource($inscricao),
                "message" => "Incricao encontrado com sucesso"
            ],200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao buscar inscrição.'], 500);
        }
    }


   
    public function update(UpdateInscricaoRequest $request, $id)
    {
        try {
            $inscricao = Inscricao::find($id);

            if(is_null($inscricao)){
                return response()->json([
                    'message' => "Incrição não encontrada"
                ]);
            }

            $data = $request->validated();

            if($inscricao->status === "Em analise"){
                return response()->json([
                'message' => 'A inscrição já está em analise'
            ], 403);
            }
            $inscricao->update($data);

            if($this->inscricaoService->isComplete($inscricao)){
                $inscricao->update(["status" => "Em analise"]);
            }else{
                 $inscricao->update(["status" => "Incompleto"]);
            }

            return response()->json([
                "data" => new InscricaoResource($inscricao),
                'message' => 'Inscricao atualizada com sucesso'],200
            );
            

        } catch (\Exception $ex) {
            return response()->json([
                'message' => 'Falha ao atualizar inscricao'
            ], 500);
        }
    }


    public function destroy(string $id)
    {
        //
    }


    public function recadastro(){ // TODO: mudar nome e criar rota
        
        try{
            Inscricao::query()->update([
                'status' => 'incompleto'
            ]);
            return response()->json([
                "message" => "Status de inscrições redefinido"
            ], 200);

        }catch(\Exception $ex) {
            return response()->json([
                'message' => 'Falha ao ativar recadastro'
            ], 500);
        };
    }


    
}