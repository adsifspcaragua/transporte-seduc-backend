<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Inscrição devolvida para correção passa a ter status próprio, Em revisao,
     * em vez de se confundir com Incompleto (lista de espera).
     *
     * Só a devolução grava campos_pendentes sem limpar; aprovar, rejeitar e o
     * reenvio completo zeram o campo. Então Incompleto com campos_pendentes é
     * uma devolução ainda não respondida pelo estudante.
     */
    public function up(): void
    {
        DB::table('inscricoes')
            ->where('status', 'Incompleto')
            ->whereNotNull('campos_pendentes')
            ->update(['status' => 'Em revisao']);
    }

    public function down(): void
    {
        DB::table('inscricoes')->where('status', 'Em revisao')->update(['status' => 'Incompleto']);
    }
};
