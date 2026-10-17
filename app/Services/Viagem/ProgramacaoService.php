<?php

namespace App\Services\Viagem;

use App\Http\Resources\Viagem\DiaSemOperacaoResource;
use App\Http\Resources\Viagem\HorarioViagemResource;
use App\Models\DiaSemOperacao;
use App\Models\HorarioViagem;
use App\Models\User;
use App\Models\Viagem;
use App\Services\Auditoria\AuditoriaService;
use App\Support\HorarioLocal;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * A programacao das linhas: horarios de ida/volta e dias sem operacao.
 *
 * Mudar a programacao nao reescreve o passado. As viagens futuras que ainda
 * podem ser refeitas (sem presenca e sem ajuste manual) sao descartadas e
 * voltam a ser geradas com a regra nova.
 */
class ProgramacaoService
{
    public function __construct(
        private readonly GeradorViagens $gerador,
        private readonly AuditoriaService $auditoria,
    ) {}

    /**
     * @param  array<string, mixed>  $filtros
     */
    public function horarios(array $filtros): JsonResponse
    {
        $horarios = HorarioViagem::with('linha:id,name')
            ->when($filtros['linha_id'] ?? null, fn ($q, $id) => $q->where('linha_id', $id))
            ->when(array_key_exists('ativo', $filtros), fn ($q) => $q->where('ativo', (bool) $filtros['ativo']))
            ->orderBy('linha_id')
            ->orderBy('horario_saida')
            ->get();

        return response()->json(['data' => HorarioViagemResource::collection($horarios)]);
    }

    public function horario(string $id): JsonResponse
    {
        $horario = HorarioViagem::with('linha:id,name')->find($id);

        if (! $horario) {
            return response()->json(['message' => 'Horário não encontrado'], 404);
        }

        return response()->json(['data' => new HorarioViagemResource($horario)]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function criarHorario(User $user, array $data): JsonResponse
    {
        try {
            $horario = HorarioViagem::create($data);
            $this->gerador->gerar(HorarioLocal::hoje(), HorarioLocal::hoje(), $horario->linha_id);
            $this->auditoria->registrar($user, 'horario.criar', $horario, $horario->only(['linha_id', 'sentido', 'horario_saida', 'dias_semana']));

            return response()->json([
                'data' => new HorarioViagemResource($horario->load('linha:id,name')),
                'message' => 'Horário cadastrado com sucesso',
            ], 201);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['message' => 'Erro ao cadastrar horário'], 500);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function atualizarHorario(User $user, string $id, array $data): JsonResponse
    {
        try {
            $horario = HorarioViagem::find($id);

            if (! $horario) {
                return response()->json(['message' => 'Horário não encontrado'], 404);
            }

            $antes = $horario->only(array_keys($data));

            $descartadas = DB::transaction(function () use ($horario, $data) {
                $horario->update($data);

                return $this->gerador->descartarFuturas($horario);
            });

            $this->gerador->gerar(HorarioLocal::hoje(), HorarioLocal::hoje(), $horario->linha_id);
            $this->auditoria->registrar($user, 'horario.atualizar', $horario, [
                'antes' => $antes,
                'depois' => $horario->only(array_keys($data)),
                'viagens_refeitas' => $descartadas,
            ]);

            return response()->json([
                'data' => new HorarioViagemResource($horario->load('linha:id,name')),
                'message' => 'Horário atualizado com sucesso',
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['message' => 'Erro ao atualizar horário'], 500);
        }
    }

    public function removerHorario(User $user, string $id): JsonResponse
    {
        try {
            $horario = HorarioViagem::find($id);

            if (! $horario) {
                return response()->json(['message' => 'Horário não encontrado'], 404);
            }

            // As viagens que ja aconteceram ficam (sem horario de origem); as
            // futuras que ainda podem ser refeitas saem junto.
            DB::transaction(function () use ($horario) {
                $this->gerador->descartarFuturas($horario);
                $horario->delete();
            });

            $this->auditoria->registrar($user, 'horario.remover', null, ['horario_id' => (int) $id, ...$horario->only(['linha_id', 'sentido', 'horario_saida'])]);

            return response()->json(['message' => 'Horário removido com sucesso']);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['message' => 'Erro ao remover horário'], 500);
        }
    }

    /**
     * @param  array<string, mixed>  $filtros
     */
    public function diasSemOperacao(array $filtros): JsonResponse
    {
        $dias = DiaSemOperacao::with(['linha:id,name', 'criadoPor:id,name'])
            ->when($filtros['de'] ?? null, fn ($q, $de) => $q->whereDate('data', '>=', $de))
            ->when($filtros['ate'] ?? null, fn ($q, $ate) => $q->whereDate('data', '<=', $ate))
            ->orderBy('data')
            ->get();

        return response()->json(['data' => DiaSemOperacaoResource::collection($dias)]);
    }

    /**
     * Feriado ou paralisacao. As viagens ja geradas do dia sao canceladas;
     * remover o dia depois nao as reativa sozinho (a gestao reativa a viagem).
     *
     * @param  array<string, mixed>  $data
     */
    public function criarDiaSemOperacao(User $user, array $data): JsonResponse
    {
        try {
            [$dia, $canceladas] = DB::transaction(function () use ($user, $data) {
                $dia = DiaSemOperacao::create([...$data, 'criado_por' => $user->id]);

                $canceladas = Viagem::whereDate('data', $dia->data)
                    ->where('status', Viagem::PROGRAMADA)
                    ->when($dia->linha_id, fn ($q) => $q->where('linha_id', $dia->linha_id))
                    ->update([
                        'status' => Viagem::CANCELADA,
                        'motivo_alteracao' => mb_substr('Sem operação: '.$dia->motivo, 0, 255),
                        'alterada_por' => $user->id,
                    ]);

                return [$dia, $canceladas];
            });

            $this->auditoria->registrar($user, 'dia_sem_operacao.criar', $dia, [
                'data' => $dia->data->toDateString(),
                'linha_id' => $dia->linha_id,
                'viagens_canceladas' => $canceladas,
            ], $dia->motivo);

            return response()->json([
                'data' => new DiaSemOperacaoResource($dia->load('linha:id,name')),
                'viagens_canceladas' => $canceladas,
                'message' => 'Dia sem operação registrado',
            ], 201);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['message' => 'Erro ao registrar dia sem operação'], 500);
        }
    }

    public function removerDiaSemOperacao(User $user, string $id): JsonResponse
    {
        $dia = DiaSemOperacao::find($id);

        if (! $dia) {
            return response()->json(['message' => 'Registro não encontrado'], 404);
        }

        $dia->delete();
        $this->auditoria->registrar($user, 'dia_sem_operacao.remover', null, [
            'data' => $dia->data->toDateString(),
            'linha_id' => $dia->linha_id,
        ]);

        return response()->json([
            'message' => 'Dia sem operação removido. Viagens canceladas por ele precisam ser reativadas manualmente.',
        ]);
    }
}
