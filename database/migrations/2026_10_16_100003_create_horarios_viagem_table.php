<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A programacao de uma linha: em que dias, em que sentido e a que horas sai.
     *
     * Ida e volta sao horarios separados, porque cada uma vira uma viagem
     * propria e a presenca e registrada por viagem. A janela de presenca pode
     * ser definida aqui; nula, vale o padrao de config/presenca.php.
     */
    public function up(): void
    {
        Schema::create('horarios_viagem', function (Blueprint $table) {
            $table->id();
            $table->foreignId('linha_id')->constrained('linhas')->cascadeOnDelete();
            $table->string('sentido', 10);
            // Dias da semana no padrao do Carbon (0 = domingo), como em
            // estudantes.days_of_week.
            $table->json('dias_semana');
            $table->time('horario_saida');
            $table->unsignedSmallInteger('janela_antes_min')->nullable();
            $table->unsignedSmallInteger('janela_depois_min')->nullable();
            $table->date('vigencia_inicio')->nullable();
            $table->date('vigencia_fim')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();

            $table->index(['linha_id', 'sentido']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('horarios_viagem');
    }
};
