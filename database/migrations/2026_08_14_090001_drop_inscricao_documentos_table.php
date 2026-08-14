<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A lista de espera não pede mais documentos: eles passaram a ser exigidos
     * somente no recadastro, já com o estudante dentro do sistema.
     */
    public function up(): void
    {
        Schema::dropIfExists('inscricao_documentos');
    }

    public function down(): void
    {
        Schema::create('inscricao_documentos', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type');
            $table->string('file_path');
            $table->string('status')->default('Em analise');
            $table->foreignId('inscricao_id')->references('id')->on('inscricoes')->cascadeDelete();
            $table->timestamps();
        });
    }
};
