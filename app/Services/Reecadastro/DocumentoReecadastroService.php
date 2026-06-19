<?php

namespace App\Services\Reecadastro;

use App\Http\Resources\Reecadastro\Documento\DocumentoResource;
use App\Models\DocumentacaoReecadastro;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;
use Throwable;

class DocumentoReecadastroService
{
    public function index(): JsonResponse|AnonymousResourceCollection
    {
        $documentos = DocumentacaoReecadastro::all();

        if ($documentos->isEmpty()) {
            return response()->json(['message' => 'Nenhum documento de recadastro cadastrado'], 200);
        }

        return DocumentoResource::collection($documentos);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function store(array $data): JsonResponse
    {
        try {
            $documento = DocumentacaoReecadastro::create($data);

            return response()->json([
                'data' => new DocumentoResource($documento),
                'message' => 'Documento de recadastro criado com sucesso',
            ], 201);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Erro ao cadastrar documento de recadastro',
            ], 500);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $documento = DocumentacaoReecadastro::find($id);

            if (! $documento) {
                return response()->json(['message' => 'Documento de recadastro não encontrado'], 404);
            }

            return response()->json([
                'data' => new DocumentoResource($documento),
                'message' => 'Documento de recadastro encontrado com sucesso',
            ], 200);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Erro ao encontrar documento de recadastro',
            ], 500);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(array $data, string $id): JsonResponse
    {
        try {
            $documento = DocumentacaoReecadastro::find($id);

            if (! $documento) {
                return response()->json(['message' => 'Documento de recadastro não encontrado'], 404);
            }

            $documento->update($data);

            return response()->json([
                'data' => new DocumentoResource($documento),
                'message' => 'Documento de recadastro atualizado com sucesso',
            ], 200);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Erro ao atualizar documento de recadastro',
            ], 500);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $documento = DocumentacaoReecadastro::find($id);

            if (! $documento) {
                return response()->json(['message' => 'Documento de recadastro não encontrado'], 404);
            }

            if ($documento->file_path) {
                Storage::delete($documento->file_path);
            }

            $documento->delete();

            return response()->json([
                'message' => 'Documento de recadastro deletado com sucesso',
            ], 200);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Erro ao deletar documento de recadastro',
            ], 500);
        }
    }
}
