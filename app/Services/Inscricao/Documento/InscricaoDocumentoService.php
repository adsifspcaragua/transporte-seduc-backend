<?php

namespace App\Services\Inscricao\Documento;

use App\Http\Requests\Inscricao\Documento\StoreDocumentoRequest;
use App\Http\Requests\Inscricao\Documento\UpdateDocumentoRequest;
use App\Http\Resources\Inscricao\Documento\DocumentoResource;
use App\Models\Inscricao;
use App\Models\InscricaoDocumento;
use App\Services\Inscricao\InscricaoStatusService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Throwable;

class InscricaoDocumentoService
{
    public function __construct(private readonly InscricaoStatusService $statusService) {}

    public function index(Inscricao $inscricao): JsonResponse
    {
        try {
            $documentos = InscricaoDocumento::where('inscricao_id', $inscricao->id)->get();

            if ($documentos->isEmpty()) {
                return response()->json('Sem documentos para essa inscrição.', 200);
            }

            return response()->json([
                'documento' => DocumentoResource::collection($documentos),
                'message' => 'Documentos exibidos com sucesso.',
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Erro ao exibir documentos',
            ], 500);
        }
    }

    public function store(StoreDocumentoRequest $request, Inscricao $inscricao): JsonResponse
    {
        try {
            $data = $request->validated();

            if ($request->hasFile('file_path')) {
                $data['file_path'] = $request->file('file_path')->store('documentos');
            }

            $data['inscricao_id'] = $inscricao->id;
            $documento = InscricaoDocumento::create($data);
            $this->statusService->refreshStatus($inscricao);
            return response()->json([
                'documento' => new DocumentoResource($documento),
                'message' => 'Documento cadastrado com sucesso.',
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Erro ao cadastrar documento',
            ], 500);
        }
    }

    public function show(string $inscricao_id, string $documento_id): JsonResponse
    {
        try {
       
            $inscricao = Inscricao::find($inscricao_id);
            $documento = InscricaoDocumento::find($documento_id);

            if(is_null($inscricao)){
                return response()->json(['message' => 'Inscricao não encontrada'], 404);
            }

            if (is_null($documento)) {
                return response()->json(['message' => 'Documento não encontrado'], 404);
            }

            return response()->json([
                'documento' => new DocumentoResource($documento),
                'message' => 'Documento exibido com sucesso.',
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Erro ao exibir documento',
            ], 500);
        }
    }

    public function update(UpdateDocumentoRequest $request, Inscricao $inscricao, InscricaoDocumento $documento): JsonResponse
    {
        try {
            if ($documento->inscricao_id !== $inscricao->id) {
                return response()->json(['message' => 'Documento não encontrado'], 404);
            }

            $data = $request->validated();

            if ($request->hasFile('file_path')) {
                if ($documento->file_path) {
                    Storage::delete($documento->file_path);
                }

                $data['file_path'] = $request->file('file_path')->store('documentos');
            }

            $documento->update([...$data, 'status' => 'Em analise']);
            $this->statusService->refreshStatus($inscricao);

            return response()->json([
                'documento' => new DocumentoResource($documento),
                'message' => 'Documento atualizado com sucesso.',
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Erro ao atualizar documento',
            ], 500);
        }
    }

    public function destroy(Inscricao $inscricao, InscricaoDocumento $documento): JsonResponse
    {
        try {
            if ($documento->inscricao_id !== $inscricao->id) {
                return response()->json(['message' => 'Documento não encontrado'], 404);
            }

            if ($documento->file_path) {
                Storage::delete($documento->file_path);
            }

            $documento->delete();
            $this->statusService->refreshStatus($inscricao);

            return response()->json([
                'message' => 'Documento deletado com sucesso.',
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Erro ao deletar documento',
            ], 500);
        }
    }
}
