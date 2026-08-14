<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Uniformiza os status gravados antes da padronização.
     *
     * Inscrição: Incompleto | Em analise | Aprovado | Rejeitado
     * Estudante: Em espera  | Ativo      | Inativo
     */
    public function up(): void
    {
        DB::table('inscricoes')->whereIn('status', ['pendente', 'Pendente'])->update(['status' => 'Incompleto']);
        DB::table('inscricoes')->where('status', 'Aprovada')->update(['status' => 'Aprovado']);
        DB::table('inscricoes')->whereIn('status', ['Rejeitada', 'Em lista de espera'])->update(['status' => 'Rejeitado']);

        DB::table('estudantes')->whereIn('status', ['Aprovado', 'ATIVO', 'ativo'])->update(['status' => 'Ativo']);
        DB::table('estudantes')->whereIn('status', ['Rejeitado', 'INATIVO', 'inativo'])->update(['status' => 'Inativo']);
    }

    public function down(): void
    {
        // Normalização de dados: não há estado anterior a restaurar.
    }
};
