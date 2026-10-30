<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Excluir o estudante nao pode levar a frequencia junto: ela sustenta a regra
     * do beneficio e os relatorios. O service recusa com uma mensagem clara, e o
     * banco fica como ultima barreira.
     *
     * So muda o vinculo com o estudante. Excluir uma chamada continua levando as
     * marcacoes dela, porque e uma acao explicita sobre a propria folha.
     */
    public function up(): void
    {
        Schema::table('frequencias', function (Blueprint $table) {
            $table->dropForeign(['estudante_id']);
            $table->foreign('estudante_id')->references('id')->on('estudantes')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('frequencias', function (Blueprint $table) {
            $table->dropForeign(['estudante_id']);
            $table->foreign('estudante_id')->references('id')->on('estudantes')->cascadeOnDelete();
        });
    }
};
