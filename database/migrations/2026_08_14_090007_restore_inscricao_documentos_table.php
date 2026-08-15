<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('inscricao_documentos')) {
            return;
        }

        Schema::create('inscricao_documentos', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type');
            $table->string('file_path');
            $table->string('nome_original')->nullable();
            $table->string('status')->default('Em analise');
            $table->foreignId('inscricao_id')->constrained('inscricoes')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['inscricao_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inscricao_documentos');
    }
};
