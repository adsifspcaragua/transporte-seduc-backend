<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Desafio de uso unico que o navegador assina com a chave privada.
     *
     * O texto assinado e montado pelo servidor (versao, proposito, estudante,
     * referencia, nonce e expiracao); aqui fica so o hash dele. O consumo e um
     * UPDATE condicional em `usado_em`: duas requisicoes com o mesmo desafio
     * nao passam as duas.
     */
    public function up(): void
    {
        Schema::create('desafios_dispositivo', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('estudante_id')->constrained('estudantes')->cascadeOnDelete();
            $table->string('proposito', 20);
            $table->unsignedTinyInteger('versao');
            $table->char('desafio_hash', 64);
            $table->foreignId('solicitacao_id')->nullable()->constrained('solicitacoes_dispositivo')->cascadeOnDelete();
            $table->foreignUuid('dispositivo_id')->nullable()->constrained('dispositivos_estudante')->cascadeOnDelete();
            $table->foreignId('viagem_id')->nullable()->constrained('viagens')->cascadeOnDelete();
            $table->foreignId('veiculo_id')->nullable()->constrained('veiculos')->cascadeOnDelete();
            $table->dateTime('expira_em');
            $table->dateTime('usado_em')->nullable();
            $table->timestamps();

            $table->index('expira_em');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('desafios_dispositivo');
    }
};
