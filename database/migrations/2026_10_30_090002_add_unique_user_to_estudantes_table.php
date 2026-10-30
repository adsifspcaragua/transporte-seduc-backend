<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Uma conta, no maximo um estudante. A presenca descobre o estudante pela
     * conta autenticada, entao uma conta com dois cadastros registraria
     * presenca em nome do que aparecesse primeiro.
     *
     * Vinculo para usuario que nao existe mais e lixo e vira nulo. Conta com
     * dois estudantes precisa de uma decisao humana: a migration para e aponta
     * quais sao, em vez de escolher um e tirar o acesso do outro.
     */
    public function up(): void
    {
        DB::table('estudantes')
            ->whereNotNull('user_id')
            ->whereNotIn('user_id', DB::table('users')->select('id'))
            ->update(['user_id' => null]);

        $duplicadas = DB::table('estudantes')
            ->whereNotNull('user_id')
            ->groupBy('user_id')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('user_id');

        if ($duplicadas->isNotEmpty()) {
            throw new RuntimeException(
                'Há contas ligadas a mais de um estudante (users.id: '.$duplicadas->implode(', ').'). Corrija estudantes.user_id e rode a migration de novo.'
            );
        }

        Schema::table('estudantes', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->change();

            $table->unique('user_id');
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('estudantes', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropUnique(['user_id']);
            $table->unsignedInteger('user_id')->nullable()->change();
        });
    }
};
