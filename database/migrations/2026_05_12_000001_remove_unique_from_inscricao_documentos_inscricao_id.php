<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('inscricao_documentos', function (Blueprint $table) {
            $table->dropForeign(['inscricao_id']);
            $table->dropUnique('inscricao_documentos_inscricao_id_unique');
            $table->foreign('inscricao_id')->references('id')->on('inscricoes')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inscricao_documentos', function (Blueprint $table) {
            $table->dropForeign(['inscricao_id']);
            $table->unique('inscricao_id');
            $table->foreign('inscricao_id')->references('id')->on('inscricoes')->cascadeOnDelete();
        });
    }
};
