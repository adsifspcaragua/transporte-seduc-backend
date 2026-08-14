<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A solicitação passa a pertencer a um período de recadastro e guarda:
     * - os pedidos de prazo adicional (matrícula e cronograma);
     * - os aceites da declaração de responsabilidade;
     * - o token da sessão pública iniciada pelo CPF;
     * - o registro de quem homologou.
     */
    public function up(): void
    {
        Schema::table('solicitacoes_reecadastro', function (Blueprint $table) {
            $table->foreignId('periodo_id')->after('estudante_id')->constrained('periodos_reecadastro')->cascadeOnDelete();
            $table->boolean('prazo_matricula')->default(false)->after('observacoes');
            $table->boolean('prazo_cronograma')->default(false)->after('prazo_matricula');
            $table->boolean('aceite_veracidade')->default(false)->after('prazo_cronograma');
            $table->boolean('aceite_ciencia')->default(false)->after('aceite_veracidade');
            $table->string('access_token', 64)->nullable()->after('aceite_ciencia');
            $table->timestamp('token_expira_em')->nullable()->after('access_token');
            $table->timestamp('enviada_em')->nullable()->after('token_expira_em');
            $table->unsignedBigInteger('analisado_por')->nullable()->after('enviada_em');
            $table->timestamp('analisado_em')->nullable()->after('analisado_por');

            $table->unique(['estudante_id', 'periodo_id']);
            $table->foreign('estudante_id')->references('id')->on('estudantes')->cascadeOnDelete();
        });

        Schema::table('solicitacoes_reecadastro', function (Blueprint $table) {
            $table->string('status')->default('Pendente')->change();
        });
    }

    public function down(): void
    {
        Schema::table('solicitacoes_reecadastro', function (Blueprint $table) {
            $table->dropForeign(['estudante_id']);
            $table->dropUnique(['estudante_id', 'periodo_id']);
            $table->dropConstrainedForeignId('periodo_id');
            $table->dropColumn([
                'prazo_matricula',
                'prazo_cronograma',
                'aceite_veracidade',
                'aceite_ciencia',
                'access_token',
                'token_expira_em',
                'enviada_em',
                'analisado_por',
                'analisado_em',
            ]);
        });
    }
};
