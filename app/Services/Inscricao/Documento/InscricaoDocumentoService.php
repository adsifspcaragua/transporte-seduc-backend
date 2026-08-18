<?php

namespace App\Services\Inscricao\Documento;

use App\Http\Requests\Inscricao\Documento\StoreDocumentoRequest;
use App\Http\Resources\Inscricao\Documento\DocumentoResource;
use App\Models\Inscricao;
use App\Models\InscricaoDocumento;
use App\Services\Inscricao\InscricaoStatusService;
use App\Support\EntregaDeArquivo;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class InscricaoDocumentoService
{
    public function __construct(private readonly InscricaoStatusService $statusService) {}

    public function index(Inscricao $inscricao): JsonResponse
    {
        return response()->json([
            'documento' => DocumentoResource::collection($inscricao->inscricao_documentos()->get()),
        ]);
    }

    public function store(StoreDocumentoRequest $request, Inscricao $inscricao): JsonResponse
    {
        try {
            $data = $request->validated();
            $arquivo = $request->file('file_path');
            $existente = $inscricao->inscricao_documentos()->where('name', $data['name'])->first();
            $caminhoAnterior = $existente?->file_path;
            $caminho = $arquivo->store("inscricoes/{$inscricao->id}");

            $documento = $inscricao->inscricao_documentos()->updateOrCreate(
                ['name' => $data['name']],
                [
                    'type' => $data['type'],
                    'file_path' => $caminho,
                    'nome_original' => $arquivo->getClientOriginalName(),
                    'status' => 'Em analise',
                ],
            );

            if ($caminhoAnterior && $caminhoAnterior !== $caminho) {
                Storage::delete($caminhoAnterior);
            }

            $this->statusService->refreshStatus($inscricao);

            return response()->json([
                'documento' => new DocumentoResource($documento),
                'message' => 'Documento enviado com sucesso.',
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['message' => 'Erro ao enviar documento.'], 500);
        }
    }

    public function destroy(Inscricao $inscricao, InscricaoDocumento $documento): JsonResponse
    {
        if ($documento->inscricao_id !== $inscricao->id) {
            return response()->json(['message' => 'Documento não encontrado.'], 404);
        }

        Storage::delete($documento->file_path);
        $documento->delete();
        $this->statusService->refreshStatus($inscricao);

        return response()->json(['message' => 'Documento removido com sucesso.']);
    }

    public function download(Inscricao $inscricao, InscricaoDocumento $documento, bool $inline = false): JsonResponse|StreamedResponse
    {
        if ($documento->inscricao_id !== $inscricao->id || ! Storage::exists($documento->file_path)) {
            return response()->json(['message' => 'Documento não encontrado.'], 404);
        }

        return EntregaDeArquivo::responder(
            $documento->file_path,
            $documento->nome_original ?? basename($documento->file_path),
            $inline,
        );
    }
}
