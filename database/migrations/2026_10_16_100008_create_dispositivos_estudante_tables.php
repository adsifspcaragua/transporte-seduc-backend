<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Vinculo do estudante a uma instalacao de navegador autorizada.
     *
     * O navegador gera um par de chaves ECDSA P-256; a privada nunca sai dele
     * e aqui fica so a publica (SPKI). Isso nao identifica o celular fisico:
     * e "uma instalacao de navegador autorizada por estudante".
     *
     * As regras "uma credencial ativa por estudante" e "uma solicitacao em
     * aberto por estudante" ficam no banco, e nao so no codigo: as colunas
     * `estudante_ativo_id` e `estudante_em_aberto_id` recebem o estudante
     * enquanto o registro esta ativo/em aberto e voltam a nulo depois, e o
     * indice unico impede duas linhas com o mesmo valor. Nao sao colunas
     * geradas porque o MySQL nao aceita coluna gerada sobre uma chave
     * estrangeira com ON DELETE CASCADE.
     */
    public function up(): void
    {
        Schema::create('solicitacoes_dispositivo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudante_id')->constrained('estudantes')->cascadeOnDelete();
            $table->unsignedBigInteger('estudante_em_aberto_id')->nullable()->unique();
            // Vinculacao: primeiro dispositivo. Troca: substitui o ativo.
            $table->string('tipo', 20);
            $table->string('status', 20)->default('Pendente');
            $table->text('motivo')->nullable();
            $table->foreignId('analisada_por')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('analisada_em')->nullable();
            $table->text('parecer')->nullable();
            $table->dateTime('aprovacao_expira_em')->nullable();
            $table->dateTime('concluida_em')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });

        Schema::create('dispositivos_estudante', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('estudante_id')->constrained('estudantes')->cascadeOnDelete();
            $table->unsignedBigInteger('estudante_ativo_id')->nullable()->unique();
            $table->foreignId('solicitacao_id')->nullable()->constrained('solicitacoes_dispositivo')->nullOnDelete();
            $table->text('chave_publica');
            // Uma chave nunca serve a duas contas, nem depois de revogada.
            $table->char('chave_publica_sha256', 64)->unique();
            // Apenas informativo ("Chrome no celular"): nao prova nada.
            $table->string('rotulo')->nullable();
            $table->string('status', 20)->default('Ativo');
            $table->foreignId('aprovado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('aprovado_em')->nullable();
            $table->dateTime('ultimo_uso_em')->nullable();
            $table->dateTime('revogado_em')->nullable();
            $table->foreignId('revogado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->string('motivo_revogacao')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dispositivos_estudante');
        Schema::dropIfExists('solicitacoes_dispositivo');
    }
};
