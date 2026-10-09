<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Separa o resultado da análise do benefício vigente.
     *
     * status:   Lista de espera | Aprovado | Recusado
     * situacao: Ativo | Inativo
     */
    public function up(): void
    {
        Schema::table('estudantes', function (Blueprint $table) {
            $table->string('situacao')->default('Inativo')->after('status');
        });

        DB::table('estudantes')->where('status', 'Ativo')->update([
            'status' => 'Aprovado',
            'situacao' => 'Ativo',
        ]);

        // Inativo por inscrição rejeitada foi recusado; os demais foram
        // aprovados e perderam o benefício depois (frequência, recadastro).
        DB::table('estudantes')
            ->where('status', 'Inativo')
            ->whereIn('inscricao_id', DB::table('inscricoes')->where('status', 'Rejeitado')->select('id'))
            ->update(['status' => 'Recusado', 'situacao' => 'Inativo']);

        DB::table('estudantes')->where('status', 'Inativo')->update([
            'status' => 'Aprovado',
            'situacao' => 'Inativo',
        ]);

        DB::table('estudantes')->where('status', 'Em espera')->update([
            'status' => 'Lista de espera',
            'situacao' => 'Inativo',
        ]);

        Schema::table('estudantes', function (Blueprint $table) {
            $table->string('status')->default('Lista de espera')->change();
        });
    }

    public function down(): void
    {
        DB::table('estudantes')->where('status', 'Lista de espera')->update(['status' => 'Em espera']);
        DB::table('estudantes')->where('status', '!=', 'Em espera')->where('situacao', 'Ativo')->update(['status' => 'Ativo']);
        DB::table('estudantes')->whereNotIn('status', ['Em espera', 'Ativo'])->update(['status' => 'Inativo']);

        Schema::table('estudantes', function (Blueprint $table) {
            $table->string('status')->default('Em espera')->change();
            $table->dropColumn('situacao');
        });
    }
};
