<?php

namespace App\Services\Viagem;

use App\Http\Resources\Viagem\ViagemResource;
use App\Models\User;
use App\Models\Viagem;
use App\Services\Auditoria\AuditoriaService;
use App\Support\HorarioLocal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Carbon;
use Throwable;

/**
 * As viagens datadas: consulta, geracao antecipada e os ajustes do dia a dia
 * (atraso, troca de van, cancelamento), sempre com motivo e auditoria.
 */
class ViagemService
{
    public function __construct(
        private readonly GeradorViagens $gerador,
        private readonly AuditoriaService $auditoria,
    ) {}

    /**
     * @param  array<string, mixed>  $filtros
     */
    public function index(array $filtros): AnonymousResourceCollection
    {
        $viagens = Viagem::with(['linha:id,name', 'veiculo:id,identificacao,placa'])
            ->withCount('presencas')
            ->when($filtros['data'] ?? null, fn ($q, $data) => $q->whereDate('data', $data))
            ->when($filtros['de'] ?? null, fn ($q, $de) => $q->whereDate('data', '>=', $de))
            ->when($filtros['ate'] ?? null, fn ($q, $ate) => $q->whereDate('data', '<=', $ate))
            ->when($filtros['linha_id'] ?? null, fn ($q, $id) => $q->where('linha_id', $id))
            ->when($filtros['veiculo_id'] ?? null, fn ($q, $id) => $q->where('veiculo_id', $id))
            ->when($filtros['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filtros['sentido'] ?? null, fn ($q, $sentido) => $q->where('sentido', $sentido))
            ->orderBy('janela_inicio')
            ->paginate($filtros['per_page'] ?? 15);

        return ViagemResource::collection($viagens);
    }

    public function show(string $id): JsonResponse
    {
        $viagem = Viagem::with(['linha:id,name', 'veiculo:id,identificacao,placa', 'alteradaPor:id,name'])
            ->withCount('presencas')
            ->find($id);

        if (! $viagem) {
            return response()->json(['message' => 'Viagem não encontrada'], 404);
        }

        return response()->json(['data' => new ViagemResource($viagem)]);
    }

    /**
     * Gera as viagens de um periodo para a gestao poder ajusta-las antes do
     * dia (trocar a van de amanha, por exemplo). Devolve os conflitos de van.
     *
     * @param  array{de: string, ate: string, linha_id?: int|null}  $data
     */
    public function gerar(array $data): JsonResponse
    {
        try {
            $de = Carbon::parse($data['de']);
            $ate = Carbon::parse($data['ate']);

            $criadas = $this->gerador->gerar($de, $ate, $data['linha_id'] ?? null);

            return response()->json([
                'data' => [
                    'criadas' => $criadas,
                    'conflitos' => $this->gerador->conflitos($de, $ate),
                ],
                'message' => "{$criadas} viagem(ns) gerada(s)",
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['message' => 'Erro ao gerar viagens'], 500);
        }
    }

    /**
     * Ajuste da viagem: janela (atraso), van (troca so desta viagem) ou status
     * (cancelar/reativar). Nao aceita deixar a van em duas viagens ao mesmo
     * tempo, senao o QR deixaria de apontar uma viagem so.
     *
     * @param  array{motivo: string, veiculo_id?: int|null, janela_inicio?: string, janela_fim?: string, status?: string}  $data
     */
    public function update(User $user, string $id, array $data): JsonResponse
    {
        try {
            $viagem = Viagem::find($id);

            if (! $viagem) {
                return response()->json(['message' => 'Viagem não encontrada'], 404);
            }

            $campos = array_intersect_key($data, array_flip(['veiculo_id', 'janela_inicio', 'janela_fim', 'status']));
            $antes = $this->retrato($viagem);

            // A gestao digita a janela na hora local.
            foreach (['janela_inicio', 'janela_fim'] as $campo) {
                if (isset($campos[$campo])) {
                    $campos[$campo] = HorarioLocal::digitado($campos[$campo]);
                }
            }

            $viagem->fill($campos);

            if (array_key_exists('veiculo_id', $campos)) {
                $viagem->veiculo_substituido = true;
            }

            if ($viagem->janela_fim->lte($viagem->janela_inicio)) {
                return response()->json([
                    'message' => 'O fim da janela precisa ser depois do início.',
                    'errors' => ['janela_fim' => ['O fim da janela precisa ser depois do início.']],
                ], 422);
            }

            if ($outra = $this->gerador->colide($viagem)) {
                return response()->json([
                    'message' => "A van já está na viagem #{$outra->id} neste horário. Ajuste a janela ou escolha outra van.",
                    'conflito_viagem_id' => $outra->id,
                ], 409);
            }

            $viagem->motivo_alteracao = $data['motivo'];
            $viagem->alterada_por = $user->id;
            $viagem->save();

            $this->auditoria->registrar($user, 'viagem.ajustar', $viagem, [
                'antes' => $antes,
                'depois' => $this->retrato($viagem),
            ], $data['motivo']);

            return response()->json([
                'data' => new ViagemResource($viagem->load(['linha:id,name', 'veiculo:id,identificacao,placa', 'alteradaPor:id,name'])),
                'message' => 'Viagem atualizada com sucesso',
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['message' => 'Erro ao atualizar viagem'], 500);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function retrato(Viagem $viagem): array
    {
        return [
            'veiculo_id' => $viagem->veiculo_id,
            'janela_inicio' => HorarioLocal::exibir($viagem->janela_inicio),
            'janela_fim' => HorarioLocal::exibir($viagem->janela_fim),
            'status' => $viagem->status,
        ];
    }
}
