<?php

namespace App\Http\Controllers\Api\Estudante;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inscricao\Documento\{StoreInscricaoDocumentoRequest, UpdateInscricaoDocumentoRequest};
use App\Http\Resources\Inscricao\InscricaoDocumentoResource;
use App\Models\Inscricao;
use App\Models\InscricaoDocumento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class InscricaoDocumentoController extends Controller
{
   
    public function index(string $inscricao_id)
    {
        try{
              $inscricao = Inscricao::findOrFail($inscricao_id);
        if($inscricao->isEmpty()) {
            abort(404, "Inscrição não encontrada");
            
        }
            $documentos = InscricaoDocumento::where('inscricao_id', $inscricao_id)->get();
    
            if($documentos->isEmpty()){
                return response()->json("Sem documentos cadastrados na inscricao.", 200);
            }
            return response()->json([
                "documento" => InscricaoDocumentoResource::collection($documentos),
                "message"   => "Documentos exibidos com sucesso."
            ]);
        }catch(\Throwable $t){
            return response()->json([
                "message" => "Erro ao exibir documentos",
                "error" => $t->getMessage()
            ], 500);
        }
        
    }

    
    public function store(StoreInscricaoDocumentoRequest $request, string $inscricao_id)
    {
       
        DB::beginTransaction();
        try{
            $inscricao = Inscricao::findOrFail($inscricao_id);
        
            $documento = InscricaoDocumento::create($request->validated());

            DB::commit();
               return response()->json([
                "documento" => new InscricaoDocumentoResource($documento),
                "message"   => "Documento cadastrado com sucesso."
            ]);

        }
        catch(\Throwable $t){
            DB::rollBack();
            return response()->json([
                "message" => "Erro ao cadastrar documento",
                "error" => $t->getMessage()
            ], 500);
        }


    }


    public function show(string $inscricao_id, string $id)
    {
        try{
           
            $inscricao = Inscricao::findOrFail($inscricao_id);
           
            $documento = InscricaoDocumento::find($id);
            if ($documento->inscricao_id != $inscricao_id) {
                
                abort("404", "Documento não encontrado para esta inscrição");
            }

            if(is_null($documento)) {
                return response()->json(["message" => "Documento não encontrada"], 404);
            }
            return response()->json([
                "documento" => new InscricaoDocumentoResource($documento),
                "message"   => "Documento exibido com sucesso."
            ]);
        }catch(\Throwable $t){
              return response()->json([
                "message" => "Erro ao exibir documento",
                "error" => $t->getMessage()
            ], 404);
        }
    }

   
    public function update(UpdateInscricaoDocumentoRequest $request, string $inscricao_id, string $id)
    {
         DB::beginTransaction();
        try{
            $inscricao = Inscricao::findOrFail($inscricao_id);
           
            $documento = InscricaoDocumento::findOrFail($id);

            if ($documento->inscricao_id != $inscricao_id) {
                abort("404", "Documento não encontrado para esta inscrição");
            }

            if(is_null($documento)) {
                return response()->json(["message" => "Documento não encontrada"], 404);
            }

            $documento->update($request->validated());

            DB::commit();
            return response()->json([
                "documento" => new InscricaoDocumentoResource($documento),
                "message"   => "Documento atualizado com sucesso."
            ]);

        }
        catch(\Throwable $t){
            DB::rollBack();
            return response()->json([
                "message" => "Erro ao atualizar documento",
                "error" => $t->getMessage()
            ], 500);
        }
    }


    public function destroy(string $inscricao_id, string $id)
    {
        try{
            $inscricao = Inscricao::findOrFail($inscricao_id);
           
            $documento = InscricaoDocumento::findOrFail($id);
            if ($documento->inscricao_id != $inscricao_id) {
                 abort("404", "Documento não encontrado para esta inscrição");
            }
            $documento_exibir = $documento;
    
            $documento->delete();

            return response()->json([
                "documento" => new InscricaoDocumentoResource($documento_exibir),
                "message"   => "Documento deletado com sucesso."
            ]);
        }
        catch(\Throwable $t){
              return response()->json([
                "message" => "Erro ao deletado documento",
                "error" => $t->getMessage()
            ], 500);
        }
    }
}