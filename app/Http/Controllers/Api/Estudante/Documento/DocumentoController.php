<?php

namespace App\Http\Controllers\Api\Estudante\Documento;

use App\Http\Controllers\Controller;
use App\Http\Requests\Documento\StoreDocumentoRequest;
use App\Http\Requests\Documento\UpdateDocumentoRequest;
use App\Http\Resources\Documento\DocumentoResource;
use App\Models\Documento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DocumentoController extends Controller
{
    
    public function index(string $estudante_id)
    {
        try{

            $documentos = Documento::where('estudante_id', $estudante_id)->get();
    
            if($documentos->isEmpty()){
                return response()->json("Sem documentos do estudante cadastrado.", 200);
            }
            return response()->json([
                "documento" => DocumentoResource::collection($documentos),
                "message"   => "Documentos exibidos com sucesso."
            ]);
        }catch(\Exception $e){
            return response()->json([
                "message" => "Erro ao exibir documentos",
                "error" => $e->getMessage()
            ], 500);
        }
        
    }

    
    public function store(StoreDocumentoRequest $request, string $estudante_id)
    {
        DB::beginTransaction();
        try{
            $documento = Documento::create($request->validate());

            DB::commit();
               return response()->json([
                "documento" => new DocumentoResource($documento),
                "message"   => "Documento cadastrado com sucesso."
            ]);

        }
        catch(\Exception $e){
            DB::rollBack();
            return response()->json([
                "message" => "Erro ao cadastrar documento",
                "error" => $e->getMessage()
            ], 500);
        }


    }


    public function show(string $id, string $estudante_id)
    {
        try{
            $documento = Documento::findOrFail($id);
            
            if ($documento->estudante_id !== $estudante_id) {
                abort(404);
            }
            return response()->json([
                "documento" => new DocumentoResource($documento),
                "message"   => "Documento exibido com sucesso."
            ]);
        }catch(\Exception $e){
              return response()->json([
                "message" => "Erro ao exibir documento",
                "error" => $e->getMessage()
            ], 500);
        }
    }

   
    public function update(UpdateDocumentoRequest $request, string $estudante_id, string $id)
    {
         DB::beginTransaction();
        try{
            $documento = Documento::findOrFail($id);

            if ($documento->estudante_id !== $estudante_id) {
                abort(404);
            }

            $documento->update($request->validate());

            DB::commit();
            return response()->json([
                "documento" => new DocumentoResource($documento),
                "message"   => "Documento atualizado com sucesso."
            ]);

        }
        catch(\Exception $e){
            DB::rollBack();
            return response()->json([
                "message" => "Erro ao atualizar documento",
                "error" => $e->getMessage()
            ], 500);
        }
    }


    public function destroy(string $estudante_id, string $id)
    {
        try{
            $documento = Documento::findOrFail($id);
            if ($documento->estudante_id !== $estudante_id) {
                abort(404);
            }
            $documento_exibir = $documento;
    
            $documento->delete();

            return response()->json([
                "documento" => new DocumentoResource($documento_exibir),
                "message"   => "Documento deletado com sucesso."
            ]);
        }
        catch(\Exception $e){
              return response()->json([
                "message" => "Erro ao deletado documento",
                "error" => $e->getMessage()
            ], 500);
        }
    }
}
