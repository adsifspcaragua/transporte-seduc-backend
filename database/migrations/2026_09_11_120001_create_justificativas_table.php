<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * O motivo de uma falta, a espera da decisao da responsavel.
     *
     * Uma por marcacao (indice unico): depois de analisada, a decisao vale e a
     * marcacao nao volta a ser justificada nem remarcada.
     *
     *  - Em analise: a falta nao conta para a perda do beneficio ate a decisao.
     *  - Aprovada: a falta e retirada; a marcacao fica como "Justificada".
     *  - Rejeitada: a justificativa e invalida; a marcacao volta a ser "Falta"
     *    e passa a contar.
     */
    public function up(): void
    {
        Schema::create('justificativas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('frequencia_id')->unique()->constrained('frequencias')->cascadeOnDelete();
            $table->text('motivo');
            $table->string('status')->default('Em analise');
            $table->foreignId('enviada_por')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('analisada_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('analisada_em')->nullable();
            // Obrigatorio na rejeicao: o estudante precisa saber por que nao valeu.
            $table->text('parecer')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('justificativas');
    }
};
