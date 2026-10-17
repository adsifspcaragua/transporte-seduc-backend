<?php

namespace App\Services\Viagem;

use App\Http\Resources\Viagem\AtribuicaoVeiculoResource;
use App\Http\Resources\Viagem\VeiculoResource;
use App\Models\AtribuicaoVeiculo;
use App\Models\Presenca;
use App\Models\User;
use App\Models\Veiculo;
use App\Services\Auditoria\AuditoriaService;
use App\Support\HorarioLocal;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * As vans, o QR fixo de cada uma e a atribuicao van x linha.
 */
class VeiculoService
{
    public function __construct(
        private readonly GeradorViagens $gerador,
        private readonly AuditoriaService $auditoria,
    ) {}

    public function index(): JsonResponse
    {
        $veiculos = Veiculo::with(['atribuicoes' => fn ($q) => $q->cobrindo(HorarioLocal::hoje())->with('linha:id,name')])
            ->orderBy('identificacao')
            ->get();

        return response()->json(['data' => VeiculoResource::collection($veiculos)]);
    }

    public function show(string $id): JsonResponse
    {
        $veiculo = Veiculo::with(['atribuicoes' => fn ($q) => $q->with('linha:id,name')->orderByDesc('inicio')])->find($id);

        if (! $veiculo) {
            return response()->json(['message' => 'Veículo não encontrado'], 404);
        }

        return response()->json(['data' => new VeiculoResource($veiculo)]);
    }

    /**
     * Cadastra a van ja com o QR. O token vem uma unica vez nesta resposta,
     * para gerar a arte de impressao; depois so rotacionando.
     *
     * @param  array<string, mixed>  $data
     */
    public function store(User $user, array $data): JsonResponse
    {
        try {
            [$veiculo, $token] = DB::transaction(function () use ($data) {
                $veiculo = Veiculo::create($data);

                return [$veiculo, $veiculo->gerarTokenQr()];
            });

            $this->auditoria->registrar($user, 'veiculo.criar', $veiculo, ['identificacao' => $veiculo->identificacao]);

            return response()->json([
                'data' => [
                    ...(new VeiculoResource($veiculo))->resolve(),
                    'qr' => $this->qr($token),
                ],
                'message' => 'Veículo cadastrado. Guarde o QR agora: o código não será exibido novamente.',
            ], 201);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['message' => 'Erro ao cadastrar veículo'], 500);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(User $user, string $id, array $data): JsonResponse
    {
        try {
            $veiculo = Veiculo::find($id);

            if (! $veiculo) {
                return response()->json(['message' => 'Veículo não encontrado'], 404);
            }

            $antes = $veiculo->only(array_keys($data));
            $veiculo->update($data);

            // Van inativada deixa de atender: as viagens futuras voltam para a
            // atribuicao seguinte, se houver.
            if (array_key_exists('ativo', $data) && $antes['ativo'] !== $veiculo->ativo) {
                $veiculo->atribuicoes()->pluck('linha_id')->unique()
                    ->each(fn ($linhaId) => $this->gerador->sincronizarVeiculos((int) $linhaId));
            }

            $this->auditoria->registrar($user, 'veiculo.atualizar', $veiculo, ['antes' => $antes, 'depois' => $veiculo->only(array_keys($data))]);

            return response()->json([
                'data' => new VeiculoResource($veiculo),
                'message' => 'Veículo atualizado com sucesso',
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['message' => 'Erro ao atualizar veículo'], 500);
        }
    }

    public function destroy(User $user, string $id): JsonResponse
    {
        try {
            $veiculo = Veiculo::find($id);

            if (! $veiculo) {
                return response()->json(['message' => 'Veículo não encontrado'], 404);
            }

            // Apagar a van tiraria o vinculo das presencas ja registradas nela.
            if (Presenca::where('veiculo_id', $veiculo->id)->exists() || $veiculo->viagens()->exists()) {
                return response()->json([
                    'message' => 'Este veículo já tem viagens ou presenças registradas. Inative-o em vez de excluir.',
                ], 409);
            }

            $veiculo->delete();
            $this->auditoria->registrar($user, 'veiculo.excluir', null, ['veiculo_id' => (int) $id, 'identificacao' => $veiculo->identificacao]);

            return response()->json(['message' => 'Veículo excluído com sucesso']);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['message' => 'Erro ao excluir veículo'], 500);
        }
    }

    /**
     * QR danificado, adesivo trocado ou suspeito: gera um token novo e o
     * antigo para de funcionar na hora. Exige reimprimir e afixar de novo.
     */
    public function rotacionarQr(User $user, string $id, string $motivo): JsonResponse
    {
        try {
            $veiculo = Veiculo::find($id);

            if (! $veiculo) {
                return response()->json(['message' => 'Veículo não encontrado'], 404);
            }

            $token = $veiculo->gerarTokenQr();
            $this->auditoria->registrar($user, 'veiculo.qr.rotacionar', $veiculo, [], $motivo);

            return response()->json([
                'data' => [
                    ...(new VeiculoResource($veiculo))->resolve(),
                    'qr' => $this->qr($token),
                ],
                'message' => 'Novo QR gerado. O anterior deixou de valer; imprima e afixe o novo na van.',
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['message' => 'Erro ao gerar novo QR'], 500);
        }
    }

    /**
     * @param  array<string, mixed>  $filtros
     */
    public function atribuicoes(array $filtros): JsonResponse
    {
        $atribuicoes = AtribuicaoVeiculo::with(['veiculo:id,identificacao,placa', 'linha:id,name', 'criadaPor:id,name'])
            ->when($filtros['veiculo_id'] ?? null, fn ($q, $id) => $q->where('veiculo_id', $id))
            ->when($filtros['linha_id'] ?? null, fn ($q, $id) => $q->where('linha_id', $id))
            ->when($filtros['data'] ?? null, fn ($q, $data) => $q->cobrindo(Carbon::parse($data)))
            ->orderByDesc('inicio')
            ->orderByDesc('id')
            ->get();

        return response()->json(['data' => AtribuicaoVeiculoResource::collection($atribuicoes)]);
    }

    /**
     * Atribuicao permanente ou troca excepcional (inicio = fim). As viagens da
     * linha que ainda nao terminaram passam a usar a nova van.
     *
     * @param  array<string, mixed>  $data
     */
    public function atribuir(User $user, array $data): JsonResponse
    {
        try {
            $atribuicao = AtribuicaoVeiculo::create([...$data, 'criada_por' => $user->id]);
            $alteradas = $this->gerador->sincronizarVeiculos($atribuicao->linha_id);

            $this->auditoria->registrar($user, 'veiculo.atribuir', $atribuicao, [
                'veiculo_id' => $atribuicao->veiculo_id,
                'linha_id' => $atribuicao->linha_id,
                'inicio' => $atribuicao->inicio->toDateString(),
                'fim' => $atribuicao->fim?->toDateString(),
                'viagens_alteradas' => $alteradas,
            ], $atribuicao->motivo);

            return response()->json([
                'data' => new AtribuicaoVeiculoResource($atribuicao->load(['veiculo:id,identificacao,placa', 'linha:id,name'])),
                'conflitos' => $this->gerador->conflitos(HorarioLocal::hoje(), HorarioLocal::hoje()->addDays(31)),
                'message' => 'Atribuição registrada',
            ], 201);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['message' => 'Erro ao registrar atribuição'], 500);
        }
    }

    /**
     * So o fim e o motivo mudam: para trocar van, linha ou inicio, encerra-se
     * esta e cria-se outra, e o historico fica legivel.
     *
     * @param  array<string, mixed>  $data
     */
    public function atualizarAtribuicao(User $user, string $id, array $data): JsonResponse
    {
        try {
            $atribuicao = AtribuicaoVeiculo::find($id);

            if (! $atribuicao) {
                return response()->json(['message' => 'Atribuição não encontrada'], 404);
            }

            if (array_key_exists('fim', $data) && $data['fim'] !== null && Carbon::parse($data['fim'])->lt($atribuicao->inicio)) {
                return response()->json([
                    'message' => 'O fim não pode ser anterior ao início da atribuição.',
                    'errors' => ['fim' => ['O fim não pode ser anterior ao início da atribuição.']],
                ], 422);
            }

            $antes = ['fim' => $atribuicao->fim?->toDateString(), 'motivo' => $atribuicao->motivo];
            $atribuicao->update($data);
            $alteradas = $this->gerador->sincronizarVeiculos($atribuicao->linha_id);

            $this->auditoria->registrar($user, 'veiculo.atribuicao.atualizar', $atribuicao, [
                'antes' => $antes,
                'depois' => ['fim' => $atribuicao->fim?->toDateString(), 'motivo' => $atribuicao->motivo],
                'viagens_alteradas' => $alteradas,
            ]);

            return response()->json([
                'data' => new AtribuicaoVeiculoResource($atribuicao->load(['veiculo:id,identificacao,placa', 'linha:id,name'])),
                'message' => 'Atribuição atualizada',
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['message' => 'Erro ao atualizar atribuição'], 500);
        }
    }

    public function removerAtribuicao(User $user, string $id): JsonResponse
    {
        try {
            $atribuicao = AtribuicaoVeiculo::find($id);

            if (! $atribuicao) {
                return response()->json(['message' => 'Atribuição não encontrada'], 404);
            }

            $atribuicao->delete();
            $alteradas = $this->gerador->sincronizarVeiculos($atribuicao->linha_id);

            $this->auditoria->registrar($user, 'veiculo.atribuicao.remover', null, [
                'atribuicao_id' => (int) $id,
                'veiculo_id' => $atribuicao->veiculo_id,
                'linha_id' => $atribuicao->linha_id,
                'viagens_alteradas' => $alteradas,
            ]);

            return response()->json(['message' => 'Atribuição removida']);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['message' => 'Erro ao remover atribuição'], 500);
        }
    }

    /**
     * @return array{token: string, url: string}
     */
    private function qr(string $token): array
    {
        return [
            'token' => $token,
            'url' => Veiculo::urlDoQr($token),
        ];
    }
}
