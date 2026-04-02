<?php

namespace App\Http\Controllers\Api\Estudante;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Inscricao;
use App\Http\Requests\{StoreInscricaoRequest, UpdateInscricaoRequest};
use App\Http\Resources\InscricaoResource;

class InscricaoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $inscricoes = Inscricao::all();
            return response()->json(InscricaoResource::collection($inscricoes),200);
            

        } catch (\Exception $ex) {
            return response()->json([
                'message' => 'Nenhuma inscrição encontrada'
            ], 404);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function store(StoreInscricaoRequest $request)
    {
         $data = $request->validated();

        try{
            
            $inscricao = new Inscricao();
            $inscricao->fill($data);
            $inscricao->save();

            return response()->json(new InscricaoResource($inscricao),201);
        }catch(\Exception $ex){
            return response()->json([
                'message' => 'Falha ao criar inscrição'
            ], 500);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $inscricao = Inscricao::find($id);
            if(!$inscricao){
                return response()->json([
                    'message' => 'Inscrição não encontrada'
                ], 404);
            }
            return response()->json(new InscricaoResource($inscricao),200);

        } catch (\Exception $ex) {
            return response()->json([
                'message' => 'Inscricao não encontrada'
            ], 404);
        }
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateInscricaoRequest $request, Inscricao $inscricao)
    {
        try {
            $data = $request->validated();

            if($this->camposPreenchidos($data)){
                $data = [...$data, "status" => "completo"];
            }

            if($inscricao->status === "completo"){
                return response()->json([
                'message' => 'A inscrição já está completa'
            ], 403);
            }
            $inscricao->update($data);
            return response()->json(new InscricaoResource($inscricao),200);

        } catch (\Exception $ex) {
            return response()->json([
                'message' => 'Falha ao atualizar inscricao'
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
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


    private function camposPreenchidos(array $inscricao){
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
            if (empty($data[$campo])) {
                return false;
            }
        }

        return $inscricao['accepted_terms'] === true && $inscricao['accepted_terms_2'] === true;
    }
}
