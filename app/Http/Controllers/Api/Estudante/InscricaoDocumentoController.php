<?php

namespace App\Http\Controllers\Estudante;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\Inscricao\InscricaoDocumentoResource;
use App\Http\Requests\Inscricao\Documento\{StoreInscricaoDocumentoRequest, UpdateInscricaoDocumentoRequest};
use App\Models\InscricaoDocumento;
use Illuminate\Support\Facades\DB;


class InscricaoDocumentoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(string $inscricao_id)
    {
        try{

            $documentos = InscricaoDocumento::where('inscricao_id', $inscricao_id)->get();
    
            if($documentos->isEmpty()){
                return response()->json("Sem documentos cadastrados na inscricao.", 200);
            }
            return response()->json([
                "documento" => InscricaoDocumentoResource::collection($documentos),
                "message"   => "Documentos exibidos com sucesso."
            ]);
        }catch(\Exception $e){
            return response()->json([
                "message" => "Erro ao exibir documentos",
                "error" => $e->getMessage()
            ], 500);
        }
        
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreInscricaoDocumentoRequest $request)
    {
        DB::beginTransaction();
        try{
            $documento = InscricaoDocumento::create($request->validate());

            DB::commit();
               return response()->json([
                "documento" => new InscricaoDocumentoResource($documento),
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

    /**
     * Display the specified resource.
     */
    public function show(string $id, string $inscricao_id)
    {
        try{
            $documento = InscricaoDocumento::find($id);
            
            if ($documento->inscricao_id !== $inscricao_id) {
                abort(404);
            }

            if(is_null($documento)) {
                return response()->json(["message" => "Documento não encontrada"], 404);
            }
            return response()->json([
                "documento" => new InscricaoDocumentoResource($documento),
                "message"   => "Documento exibido com sucesso."
            ]);
        }catch(\Exception $e){
              return response()->json([
                "message" => "Erro ao exibir documento",
                "error" => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateInscricaoDocumentoRequest $request, string $inscricao_id, string $id)
    {
         DB::beginTransaction();
        try{
            $documento = InscricaoDocumento::findOrFail($id);

            if ($documento->inscricao_id !== $inscricao_id) {
                abort(404);
            }

            if(is_null($documento)) {
                return response()->json(["message" => "Documento não encontrada"], 404);
            }

            $documento->update($request->validate());

            DB::commit();
            return response()->json([
                "documento" => new InscricaoDocumentoResource($documento),
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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $inscricao_id, string $id)
    {
        try{
            $documento = InscricaoDocumento::findOrFail($id);
            if ($documento->inscricao_id !== $inscricao_id) {
                abort(404);
            }
            $documento_exibir = $documento;
    
            $documento->delete();

            return response()->json([
                "documento" => new InscricaoDocumentoResource($documento_exibir),
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