<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Uma chamada por linha por dia — a folha de presenca do motorista.
     *
     * Enquanto esta "Aberta" o motorista marca e corrige; "Fechada" e a folha
     * entregue, e so volta a aceitar marcacao se for reaberta. O indice unico
     * (linha_id, data) garante que nao existam duas folhas do mesmo dia para a
     * mesma linha.
     */
    public function up(): void
    {
        Schema::create('chamadas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('linha_id')->constrained('linhas')->cascadeOnDelete();
            $table->date('data');
            $table->string('status')->default('Aberta');
            // Quem abriu a folha: o motorista da linha, ou a secretaria quando
            // lanca no lugar dele. Nulo se o usuario for removido depois.
            $table->foreignId('registrada_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('fechada_em')->nullable();
            $table->text('observacoes')->nullable();
            $table->timestamps();

            $table->unique(['linha_id', 'data']);
            $table->index('data');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chamadas');
    }
};
