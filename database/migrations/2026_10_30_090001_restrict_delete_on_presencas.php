<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Excluir estudante ou viagem nao pode levar a presenca junto.
     *
     * Os services ja recusam a exclusao com uma mensagem clara; o banco fica
     * como ultima barreira, inclusive para a cascata linha -> viagem -> presenca.
     */
    public function up(): void
    {
        Schema::table('presencas', function (Blueprint $table) {
            $table->dropForeign(['estudante_id']);
            $table->dropForeign(['viagem_id']);

            $table->foreign('estudante_id')->references('id')->on('estudantes')->restrictOnDelete();
            $table->foreign('viagem_id')->references('id')->on('viagens')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('presencas', function (Blueprint $table) {
            $table->dropForeign(['estudante_id']);
            $table->dropForeign(['viagem_id']);

            $table->foreign('estudante_id')->references('id')->on('estudantes')->cascadeOnDelete();
            $table->foreign('viagem_id')->references('id')->on('viagens')->cascadeOnDelete();
        });
    }
};
