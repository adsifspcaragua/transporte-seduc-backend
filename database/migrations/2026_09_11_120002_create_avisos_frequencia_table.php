<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * O que a regra de frequencia ja fez com o estudante em cada mes.
     *
     * Serve a duas coisas: impedir que o mesmo aviso seja enviado a cada chamada
     * fechada (indice unico por estudante, mes e tipo) e deixar registrado por
     * que o estudante perdeu o beneficio, com os numeros do momento.
     */
    public function up(): void
    {
        Schema::create('avisos_frequencia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudante_id')->constrained('estudantes')->cascadeOnDelete();
            // Mes de referencia no formato AAAA-MM: a contagem recomeca a cada mes.
            $table->char('referencia', 7);
            $table->string('tipo');
            $table->unsignedSmallInteger('faltas_no_mes');
            $table->unsignedSmallInteger('faltas_seguidas');
            $table->string('email')->nullable();
            $table->timestamps();

            $table->unique(['estudante_id', 'referencia', 'tipo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('avisos_frequencia');
    }
};
