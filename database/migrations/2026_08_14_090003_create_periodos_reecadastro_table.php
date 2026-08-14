<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Cada período representa uma das duas janelas anuais de recadastro.
     * Somente um período pode estar aberto por vez.
     */
    public function up(): void
    {
        Schema::create('periodos_reecadastro', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('ano');
            $table->unsignedTinyInteger('semestre');
            $table->date('data_inicio');
            $table->date('data_fim');
            $table->string('status')->default('Fechado');
            $table->text('observacoes')->nullable();
            $table->timestamps();

            $table->unique(['ano', 'semestre']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('periodos_reecadastro');
    }
};
