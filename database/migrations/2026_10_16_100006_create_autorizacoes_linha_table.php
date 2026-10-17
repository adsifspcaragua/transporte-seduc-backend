<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Permissao temporaria para o estudante usar outra linha alem da sua.
     *
     * A linha do cadastro (estudantes.linha_id) continua sendo a autorizacao
     * principal; aqui ficam os remanejamentos com prazo (D06).
     */
    public function up(): void
    {
        Schema::create('autorizacoes_linha', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudante_id')->constrained('estudantes')->cascadeOnDelete();
            $table->foreignId('linha_id')->constrained('linhas')->cascadeOnDelete();
            $table->date('inicio');
            $table->date('fim')->nullable();
            $table->string('motivo');
            $table->foreignId('criada_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['estudante_id', 'inicio']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('autorizacoes_linha');
    }
};
