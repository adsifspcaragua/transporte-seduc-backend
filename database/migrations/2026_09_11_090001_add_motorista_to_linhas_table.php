<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Vincula a linha ao motorista que a conduz.
     *
     * A chamada do dia e feita pelo motorista, e ele so pode lancar a da sua
     * propria linha: sem este vinculo nao ha como saber qual linha e a dele.
     * Fica nulo enquanto a linha nao tiver motorista definido, e vira nulo se o
     * usuario for removido — a linha continua existindo sem condutor.
     */
    public function up(): void
    {
        Schema::table('linhas', function (Blueprint $table) {
            $table->foreignId('motorista_id')
                ->nullable()
                ->after('max_capacity')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('linhas', function (Blueprint $table) {
            $table->dropForeign(['motorista_id']);
            $table->dropColumn('motorista_id');
        });
    }
};
