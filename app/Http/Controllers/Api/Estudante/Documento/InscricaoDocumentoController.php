<?php

namespace App\Http\Controllers\Api\Estudante\Documento;

use App\Http\Controllers\Controller;
use App\Http\Requests\Documento\{StoreDocumentoRequest, UpdateDocumentoRequest};
use App\Http\Resources\Documento\DocumentoResource;
use App\Models\{Inscricao, InscricaoDocumento};
use App\Services\InscricaoService;
use Illuminate\Support\Facades\Storage;

class InscricaoDocumentoController extends Controller
{

    private $inscricaoService;

    public function __construct(InscricaoService $inscricaoService){

        $this->inscricaoService = $inscricaoService;
    }
    public function index(Inscricao $inscricao)
    {
        try {
            $documentos = InscricaoDocumento::where('inscricao_id', $inscricao->id)->get();

            if ($documentos->isEmpty()) {
                return response()->json("Sem documentos para essa inscrição.", 200);
            }

            return response()->json([
                "documento" => DocumentoResource::collection($documentos),
                "message"   => "Documentos exibidos com sucesso."
            ]);

        } catch (\Exception $e) {
            return response()->json([
                "message" => "Erro ao exibir documentos",
                "error" => $e->getMessage()
            ], 500);
        }
    }

    public function store(StoreDocumentoRequest $request, Inscricao $inscricao)
    {
        try {
            $data = $request->validated();

            // upload do arquivo
            if ($request->hasFile('file_path')) {
                $path = $request->file('file_path')->store('documentos');
                $data['file_path'] = $path;
            }

            // vínculo com inscrição
            $data['inscricao_id'] = $inscricao->id;

            

            $documento = InscricaoDocumento::create($data);


            $inscricaoC = Inscricao::find($inscricao->id);
            if($this->inscricaoService->isComplete($inscricaoC)){
                $inscricaoC->update(["status" => "Em analise"]);
            }else{
                $inscricaoC->update(["status" => "Incompleto"]);
            }

            return response()->json([
                "documento" => new DocumentoResource($documento),
                "message"   => "Documento cadastrado com sucesso."
            ]);

        } catch (\Exception $e) {
            return response()->json([
                "message" => "Erro ao cadastrar documento",
                "error" => $e->getMessage()
            ], 500);
        }
    }

    public function show(Inscricao $inscricao, InscricaoDocumento $documento)
    {
        try {
            if ($documento->inscricao_id !== $inscricao->id) {
                abort(404);
            }

            return response()->json([
                "documento" => new DocumentoResource($documento),
                "message"   => "Documento exibido com sucesso."
            ]);

        } catch (\Exception $e) {
            return response()->json([
                "message" => "Erro ao exibir documento",
                "error" => $e->getMessage()
            ], 500);
        }
    }

    public function update(UpdateDocumentoRequest $request, Inscricao $inscricao, InscricaoDocumento $documento)
    {
        try {
            if ($documento->inscricao_id !== $inscricao->id) {
                abort(404);
            }

            $data = $request->validated();

            if ($request->hasFile('file_path')) {

                // remove antigo
                if ($documento->file_path) {
                    Storage::delete($documento->file_path);
                }

                // salva novo
                $data['file_path'] = $request
                    ->file('file_path')
                    ->store('documentos');
            }

            $documento->update($data);


            $inscricaoC = Inscricao::find($inscricao->id);
            if($this->inscricaoService->isComplete($inscricaoC)){
                $inscricaoC->update(["status" => "Em analise"]);
            }else{
                $inscricaoC->update(["status" => "Incompleto"]);
            }
            return response()->json([
                "documento" => new DocumentoResource($documento),
                "message"   => "Documento atualizado com sucesso."
            ]);

        } catch (\Exception $e) {
            return response()->json([
                "message" => "Erro ao atualizar documento",
                "error" => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Inscricao $inscricao, InscricaoDocumento $documento)
    {
        try {
            if ($documento->inscricao_id !== $inscricao->id) {
                abort(404);
            }

            // remove arquivo físico
            if ($documento->file_path) {
                Storage::delete($documento->file_path);
            }

            $documento->delete();

            return response()->json([
                "message" => "Documento deletado com sucesso."
            ]);

        } catch (\Exception $e) {
            return response()->json([
                "message" => "Erro ao deletar documento",
                "error" => $e->getMessage()
            ], 500);
        }
    }
}