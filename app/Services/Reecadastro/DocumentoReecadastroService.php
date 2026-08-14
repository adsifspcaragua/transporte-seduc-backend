<?php

namespace App\Services\Reecadastro;

use App\Http\Resources\Reecadastro\Documento\DocumentoResource;
use App\Models\DocumentacaoReecadastro;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class DocumentoReecadastroService
{
    /**
     * @param  array<string, mixed>  $filtros
     */
    public function index(array $filtros = []): JsonResponse|AnonymousResourceCollection
    {
        $documentos = DocumentacaoReecadastro::query()
            ->when($filtros['solicitacao_id'] ?? null, fn ($query, $id) => $query->where('solicitacao_id', $id))
            ->when($filtros['estudante_id'] ?? null, fn ($query, $id) => $query->where('estudante_id', $id))
            ->latest('id')
            ->get();

        if ($documentos->isEmpty()) {
            return response()->json(['message' => 'Nenhum documento de recadastro cadastrado'], 200);
        }

        return DocumentoResource::collection($documentos);
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
     * Entrega o arquivo enviado pelo estudante. Os documentos ficam em disco
     * privado, então esta é a única forma de acessá-los.
     */
    public function download(string $id): JsonResponse|StreamedResponse
    {
        try {
            $documento = DocumentacaoReecadastro::find($id);

            if (! $documento) {
                return response()->json(['message' => 'Documento de recadastro não encontrado'], 404);
            }

            if (! $documento->file_path || ! Storage::exists($documento->file_path)) {
                return response()->json(['message' => 'Arquivo do documento não encontrado'], 404);
            }

            return Storage::download($documento->file_path, $documento->nome_original ?: basename($documento->file_path));
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Erro ao baixar documento de recadastro',
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
