<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Guarda quais campos do cadastro a responsavel pediu para o estudante corrigir.
 *
 * O motivo em texto livre ja existia, mas nao dizia ONDE esta o erro: o estudante
 * lia "dados incorretos" e tinha de adivinhar o campo.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('solicitacoes_reecadastro', function (Blueprint $table) {
            $table->json('campos_pendentes')->nullable()->after('observacoes');
        });
    }

    public function down(): void
    {
        Schema::table('solicitacoes_reecadastro', function (Blueprint $table) {
            $table->dropColumn('campos_pendentes');
        });
    }
};
