<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Feriados, recessos e paralisacoes. Linha nula vale para todas.
     *
     * Nao sao geradas viagens nesses dias, e as ja geradas sao canceladas.
     */
    public function up(): void
    {
        Schema::create('dias_sem_operacao', function (Blueprint $table) {
            $table->id();
            $table->date('data');
            $table->foreignId('linha_id')->nullable()->constrained('linhas')->cascadeOnDelete();
            $table->string('motivo');
            $table->foreignId('criado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('data');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dias_sem_operacao');
    }
};
