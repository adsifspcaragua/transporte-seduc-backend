<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Cada arquivo passa a pertencer a uma solicitação (e, por ela, a um
     * período), tem situação própria na conferência e guarda o nome original
     * enviado pelo estudante. Um tipo de documento por solicitação.
     */
    public function up(): void
    {
        Schema::table('documentacoes_reecadastro', function (Blueprint $table) {
            $table->foreignId('solicitacao_id')->after('estudante_id')->constrained('solicitacoes_reecadastro')->cascadeOnDelete();
            $table->string('nome_original')->nullable()->after('file_path');
            $table->string('status')->default('Enviado')->after('nome_original');
            $table->text('observacoes')->nullable()->after('status');

            $table->unique(['solicitacao_id', 'type']);
            $table->foreign('estudante_id')->references('id')->on('estudantes')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('documentacoes_reecadastro', function (Blueprint $table) {
            $table->dropForeign(['estudante_id']);
            $table->dropUnique(['solicitacao_id', 'type']);
            $table->dropConstrainedForeignId('solicitacao_id');
            $table->dropColumn(['nome_original', 'status', 'observacoes']);
        });
    }
};
