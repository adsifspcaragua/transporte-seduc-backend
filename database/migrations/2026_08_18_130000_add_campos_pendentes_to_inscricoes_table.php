<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Guarda quais campos da inscricao a responsavel pediu para o estudante corrigir.
 *
 * Mesmo motivo da coluna equivalente em solicitacoes_reecadastro: o motivo em
 * texto livre diz o que houve, mas nao diz ONDE esta o erro.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inscricoes', function (Blueprint $table) {
            $table->json('campos_pendentes')->nullable()->after('observation');
        });
    }

    public function down(): void
    {
        Schema::table('inscricoes', function (Blueprint $table) {
            $table->dropColumn('campos_pendentes');
        });
    }
};
