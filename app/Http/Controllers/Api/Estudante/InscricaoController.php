<?php

namespace App\Http\Controllers\Api\Estudante;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Inscricao;
use App\Http\Requests\Inscricao\{StoreInscricaoRequest, UpdateInscricaoRequest};
use App\Http\Resources\Inscricao\InscricaoResource;

class InscricaoController extends Controller
{
    
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


   
    public function update(UpdateInscricaoRequest $request, Inscricao $inscricao)
    {
        try {
            $data = $request->validated();

            
            if($inscricao->status === "completo"){
                return response()->json([
                'message' => 'A inscrição já está completa'
            ], 403);
            }
            $inscricao->update($data);

            if($this->camposPreenchidos($inscricao)){
                $inscricao->update(["status" => "completo"]);
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


    private function camposPreenchidos(Inscricao $inscricao){
        $camposObrigatorios = [
            'name',
            'cpf',
            'rg',
            'date_of_birth',
            'phone',
            'email',
            'cep',
            'address',
            'neighborhood',
            'city',
            'number',
        ];

        foreach ($camposObrigatorios as $campo) {

            if (empty($inscricao[$campo])) {
                return false;
            }
        }

        return $inscricao['accepted_terms'] === true && $inscricao['accepted_terms_2'] === true;
    }
}
