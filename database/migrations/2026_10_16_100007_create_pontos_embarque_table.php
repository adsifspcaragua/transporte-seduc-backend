<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pontos fixos de embarque e a geocerca circular de cada um (D01/D03).
     *
     * O raio e obrigatorio e proprio do ponto: nao ha raio padrao, porque o
     * valor depende da densidade urbana e dos testes de campo.
     *
     * `linha_ponto` diz em quais linhas e sentidos o ponto e usado. A ida
     * embarca nos bairros e a volta na instituicao, entao o mesmo ponto pode
     * valer so para um dos sentidos. O horario previsto e opcional (D04):
     * quando existe, a presenca naquele ponto tambem respeita a sua janela.
     */
    public function up(): void
    {
        Schema::create('pontos_embarque', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->unsignedInteger('raio_metros');
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });

        Schema::create('linha_ponto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('linha_id')->constrained('linhas')->cascadeOnDelete();
            $table->foreignId('ponto_id')->constrained('pontos_embarque')->cascadeOnDelete();
            $table->string('sentido', 10);
            $table->unsignedSmallInteger('ordem')->nullable();
            $table->time('horario_previsto')->nullable();
            $table->unsignedSmallInteger('janela_antes_min')->nullable();
            $table->unsignedSmallInteger('janela_depois_min')->nullable();
            $table->timestamps();

            $table->unique(['linha_id', 'ponto_id', 'sentido']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('linha_ponto');
        Schema::dropIfExists('pontos_embarque');
    }
};
