<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A ocorrencia datada de um horario: a ida da Linha Centro em 16/10, por
     * exemplo. E nela que a presenca e registrada.
     *
     * A janela e copiada do horario ao gerar e pode ser ajustada pela gestao
     * (atraso, manutencao) sem tocar na programacao.
     *
     * `veiculo_id` e a van que faz a viagem: vem da atribuicao vigente da
     * linha no dia e acompanha mudancas nela enquanto a viagem nao passou.
     * Quando a gestao troca a van so desta viagem, `veiculo_substituido` fica
     * verdadeiro e a atribuicao deixa de sobrescreve-la. Nula, nenhum QR leva
     * a esta viagem.
     */
    public function up(): void
    {
        Schema::create('viagens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('horario_id')->nullable()->constrained('horarios_viagem')->nullOnDelete();
            $table->foreignId('linha_id')->constrained('linhas')->cascadeOnDelete();
            $table->string('sentido', 10);
            $table->date('data');
            $table->time('horario_saida');
            $table->dateTime('janela_inicio');
            $table->dateTime('janela_fim');
            $table->foreignId('veiculo_id')->nullable()->constrained('veiculos')->nullOnDelete();
            $table->boolean('veiculo_substituido')->default(false);
            $table->string('status')->default('Programada');
            $table->string('motivo_alteracao')->nullable();
            $table->foreignId('alterada_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['horario_id', 'data']);
            $table->index(['data', 'status']);
            $table->index(['veiculo_id', 'data']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('viagens');
    }
};
