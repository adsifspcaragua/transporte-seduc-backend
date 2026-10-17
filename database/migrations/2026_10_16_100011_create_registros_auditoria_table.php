<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Trilha das acoes administrativas sensiveis: aprovar ou revogar
     * dispositivo, trocar van, ajustar janela, presenca manual, rotacionar QR.
     *
     * Guarda o minimo: quem, o que, em qual registro, os valores alterados e a
     * justificativa. Nunca chaves, tokens ou coordenadas.
     */
    public function up(): void
    {
        Schema::create('registros_auditoria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('acao', 60);
            $table->string('auditavel_type')->nullable();
            // Texto porque ha registros com chave UUID (dispositivos).
            $table->string('auditavel_id', 36)->nullable();
            $table->json('dados')->nullable();
            $table->text('justificativa')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('acao');
            $table->index(['auditavel_type', 'auditavel_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registros_auditoria');
    }
};
