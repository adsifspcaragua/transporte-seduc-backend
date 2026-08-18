<?php

namespace App\Services\Reecadastro;

use App\Models\Estudante;
use App\Models\PeriodoReecadastro;
use App\Models\SolicitacaoReecadastro;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Quem esta ativo e nao concluiu o recadastro do periodo.
 *
 * A solicitacao so nasce quando o estudante acessa pelo CPF, entao quem nunca
 * entrou nao gera registro nenhum e some da tela de solicitacoes. Sem esta
 * consulta a responsavel nao tem como saber quem faltou.
 */
class AusentesReecadastroService
{
    /**
     * Situacoes que contam como recadastro em andamento.
     *
     * "Em analise" esta com a responsavel, nao com o estudante: quem esta nessa
     * fila nao pode ser tratado como ausente.
     */
    private const EM_ANDAMENTO = ['Aprovado', 'Em analise'];

    public function index(string $periodoId): JsonResponse
    {
        $periodo = PeriodoReecadastro::find($periodoId);

        if (! $periodo) {
            return response()->json(['message' => 'Período de recadastro não encontrado'], 404);
        }

        return response()->json([
            'data' => $this->ausentes($periodo)->map(fn (Estudante $estudante) => [
                'id' => $estudante->id,
                'name' => $estudante->name,
                'cpf' => $estudante->cpf,
                'email' => $estudante->email,
                'phone' => $estudante->phone,
                'situacao' => $this->situacao($estudante, $periodo),
            ])->values(),
            'periodo' => [
                'id' => $periodo->id,
                'referencia' => $periodo->referencia,
                'status' => $periodo->status,
            ],
        ]);
    }

    /**
     * Inativa apenas os estudantes indicados, e apenas se de fato estiverem
     * ausentes: assim um id solto no payload nao derruba quem ja recadastrou.
     */
    public function inativar(string $periodoId, array $estudanteIds): JsonResponse
    {
        $periodo = PeriodoReecadastro::find($periodoId);

        if (! $periodo) {
            return response()->json(['message' => 'Período de recadastro não encontrado'], 404);
        }

        $ausentes = $this->ausentes($periodo)->pluck('id');
        $alvos = $ausentes->intersect($estudanteIds)->values();
        $ignorados = collect($estudanteIds)->diff($ausentes)->values();

        if ($alvos->isEmpty()) {
            return response()->json([
                'message' => 'Nenhum dos estudantes informados está sem recadastro neste período.',
                'ignorados' => $ignorados,
            ], 422);
        }

        DB::transaction(function () use ($alvos) {
            Estudante::whereIn('id', $alvos)->update(['status' => 'Inativo']);
        });

        return response()->json([
            'message' => "{$alvos->count()} estudante(s) inativado(s) por falta de recadastro.",
            'inativados' => $alvos,
            'ignorados' => $ignorados,
        ]);
    }

    /**
     * Ativos que nao tem recadastro aprovado nem em analise no periodo.
     *
     * @return Collection<int, Estudante>
     */
    private function ausentes(PeriodoReecadastro $periodo)
    {
        $emDia = SolicitacaoReecadastro::where('periodo_id', $periodo->id)
            ->whereIn('status', self::EM_ANDAMENTO)
            ->pluck('estudante_id');

        return Estudante::where('status', 'Ativo')
            ->whereNotIn('id', $emDia)
            ->orderBy('name')
            ->get();
    }

    /**
     * Diz em que pe o estudante parou, para a responsavel decidir com contexto.
     */
    private function situacao(Estudante $estudante, PeriodoReecadastro $periodo): string
    {
        $solicitacao = SolicitacaoReecadastro::where('periodo_id', $periodo->id)
            ->where('estudante_id', $estudante->id)
            ->first();

        return match ($solicitacao?->status) {
            null => 'Não iniciou',
            'Pendente' => 'Começou e não enviou',
            'Pendencia' => 'Devolvido e não corrigiu',
            'Rejeitado' => 'Rejeitado',
            default => $solicitacao->status,
        };
    }
}
