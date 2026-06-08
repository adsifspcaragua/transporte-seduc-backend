<?php

namespace App\Services\Inscricao;

use App\Http\Resources\Inscricao\InscricaoResource;
use App\Models\Inscricao;
use App\Models\InscricaoDocumento;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Throwable;

class InscricaoService
{
    public function __construct(private readonly InscricaoStatusService $statusService) {}

    public function index(): JsonResponse|AnonymousResourceCollection
    {
        try {
            $inscricoes = Inscricao::all();

            if ($inscricoes->isEmpty()) {
                return response()->json(['message' => 'Nenhuma inscricao cadastrada'], 200);
            }

            return InscricaoResource::collection($inscricoes);
        } catch (Throwable) {
            return response()->json([
                'message' => 'Nenhuma inscrição encontrada',
            ], 404);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function store(array $data): JsonResponse
    {
        try {
            $inscricao = Inscricao::create($data);
            $inscricao = $this->statusService->refreshStatus($inscricao);

            return response()->json(new InscricaoResource($inscricao), 201);
        } catch (Throwable $ex) {
            return response()->json([
                'message' => 'Falha ao criar inscrição',
                'error' => $ex->getMessage(),
            ], 500);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $inscricao = Inscricao::find($id);

            if (! $inscricao) {
                return response()->json(['message' => 'Inscricao não encontrada'], 404);
            }

            return response()->json([
                'data' => new InscricaoResource($inscricao),
                'message' => 'Incricao encontrado com sucesso',
            ], 200);
        } catch (Throwable $ex) {
            return response()->json(['message' => 'Erro ao buscar inscrição.',
            ], 500);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(array $data, string $id): JsonResponse
    {
        try {
            $inscricao = Inscricao::find($id);

            if (! $inscricao) {
                return response()->json([
                    'message' => 'Incrição não encontrada',
                ], 404);
            }

            if ($inscricao->status === 'Em analise') {
                return response()->json([
                    'message' => 'A inscrição já está em analise',
                ], 403);
            }

            if ($inscricao->status === 'Em lista de espera') {
                return response()->json([
                    'message' => 'A inscrição já está aprovada',
                ], 403);
            }

            $inscricao->update($data);

            $inscricao = $this->statusService->refreshStatus($inscricao);

            return response()->json([
                'data' => new InscricaoResource($inscricao),
                'message' => 'Inscricao atualizada com sucesso',
            ], 200);
        } catch (Throwable) {
            return response()->json([
                'message' => 'Falha ao atualizar inscricao',
            ], 500);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $inscricao = Inscricao::find($id);

            if (! $inscricao) {
                return response()->json(['message' => 'Inscricao não encontrada'], 404);
            }

            $inscricao->delete();

            return response()->json([
                'message' => 'Inscricao deletada com sucesso',
            ], 200);
        } catch (Throwable) {
            return response()->json([
                'message' => 'Falha ao deletar inscricao',
            ], 500);
        }
    }

    public function recadastro(): JsonResponse
    {
        try {
            Inscricao::query()->update([
                'status' => 'Incompleto',
                'accepted_terms' => false,
                'accepted_terms_2' => false,
            ]);

            //$docs = InscricaoDocumento::query()->delete();

            return response()->json([
                'message' => 'Status de inscrições redefinido',
            ], 200);
        } catch (Throwable) {
            return response()->json([
                'message' => 'Falha ao ativar recadastro',
            ], 500);
        }
    }


    public function analiseInscricao(string $id, array $data): JsonResponse
    {
        try{
            
            $inscricao = Inscricao::find($id);
            $docs = $inscricao->inscricao_documentos;
            if ($data['decisao'] == "Aprovado"){
                $inscricao->update(['status' => "Em lista de espera", 'observation' => ""]);
                foreach($docs as $doc){
                    $doc->update([
                        'status' => 'Aprovado'
                    ]);
                }
                        
            }else{
                $inscricao->update([
                    'status' => "Reprovado",
                    'observation' => $data['motivo']
                ]);
                $docs->each(function($doc){
                    $doc->update(['status' => 'Aprovado']);
                });
                
                if (!is_null($data["documentos"])){
                    foreach($data["documentos"] as $d){
                        $alterado = $docs->firstWhere('name', $d);
                        if($alterado){
                            $alterado->update(['status' => 'Reprovado']);
                        }
                    }
                }

            }
            return response()->json([
                    'message' => 'Status de inscrição alterado',
                ], 200);

        }catch(Throwable $ex){
            return response()->json([
                'message' => 'Falha ao registrar decisão',
                'ex'=> $ex
            ], 500);
        }
    }
}
