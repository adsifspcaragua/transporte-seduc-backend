<?php

namespace App\Services\Auditoria;

use App\Http\Resources\Auditoria\RegistroAuditoriaResource;
use App\Models\RegistroAuditoria;
use App\Models\User;
use App\Support\HorarioLocal;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Carbon;

/**
 * Trilha das acoes administrativas sensiveis do modulo de presenca.
 *
 * Quem chama decide o que vai em `dados`: so o necessario para entender a
 * mudanca (antes/depois de um campo, o id afetado). Nunca chave, token ou
 * coordenada.
 */
class AuditoriaService
{
    /**
     * @param  array<string, mixed>  $dados
     */
    public function registrar(?User $user, string $acao, ?Model $alvo = null, array $dados = [], ?string $justificativa = null): RegistroAuditoria
    {
        return RegistroAuditoria::create([
            'user_id' => $user?->id,
            'acao' => $acao,
            'auditavel_type' => $alvo ? $alvo->getMorphClass() : null,
            'auditavel_id' => $alvo ? (string) $alvo->getKey() : null,
            'dados' => $dados ?: null,
            'justificativa' => $justificativa,
        ]);
    }

    /**
     * @param  array<string, mixed>  $filtros
     */
    public function index(array $filtros): AnonymousResourceCollection
    {
        $registros = RegistroAuditoria::with('user:id,name')
            ->when($filtros['acao'] ?? null, fn ($q, $acao) => $q->where('acao', 'like', $acao.'%'))
            ->when($filtros['user_id'] ?? null, fn ($q, $userId) => $q->where('user_id', $userId))
            ->when($filtros['de'] ?? null, fn ($q, $de) => $q->where('created_at', '>=', HorarioLocal::inicioDoDia($de)))
            ->when($filtros['ate'] ?? null, fn ($q, $ate) => $q->where('created_at', '<', HorarioLocal::inicioDoDia(Carbon::parse($ate)->addDay())))
            ->orderByDesc('id')
            ->paginate($filtros['per_page'] ?? 15);

        return RegistroAuditoriaResource::collection($registros);
    }
}
