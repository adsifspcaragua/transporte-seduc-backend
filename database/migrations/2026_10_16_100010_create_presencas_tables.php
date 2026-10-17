<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Presencas registradas e tentativas (aceitas ou negadas).
     *
     * Nenhuma das duas guarda latitude/longitude: para os relatorios bastam o
     * resultado da geocerca, o ponto, a distancia e a precisao informada
     * (minimo necessario, D10). Se a gestao decidir reter coordenadas, a
     * decisao precisa de finalidade, base legal e prazo antes da coluna.
     *
     * O indice unico (estudante_id, viagem_id) e o que barra a presenca dupla,
     * inclusive com duas requisicoes simultaneas.
     */
    public function up(): void
    {
        Schema::create('presencas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudante_id')->constrained('estudantes')->cascadeOnDelete();
            $table->foreignId('viagem_id')->constrained('viagens')->cascadeOnDelete();
            $table->foreignId('veiculo_id')->nullable()->constrained('veiculos')->nullOnDelete();
            $table->foreignUuid('dispositivo_id')->nullable()->constrained('dispositivos_estudante')->nullOnDelete();
            $table->foreignId('ponto_id')->nullable()->constrained('pontos_embarque')->nullOnDelete();
            // Dispositivo: QR + assinatura + horario + geocerca. Manual:
            // excecao administrativa com justificativa.
            $table->string('metodo', 20);
            $table->dateTime('registrada_em');
            $table->string('politica_geo', 30)->nullable();
            $table->string('resultado_geo', 30)->nullable();
            $table->unsignedInteger('distancia_metros')->nullable();
            $table->unsignedInteger('precisao_metros')->nullable();
            $table->foreignId('registrada_por')->nullable()->constrained('users')->nullOnDelete();
            $table->text('justificativa')->nullable();
            $table->timestamps();

            $table->unique(['estudante_id', 'viagem_id']);
            $table->index('registrada_em');
        });

        Schema::create('tentativas_presenca', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudante_id')->constrained('estudantes')->cascadeOnDelete();
            $table->foreignId('viagem_id')->nullable()->constrained('viagens')->nullOnDelete();
            $table->foreignId('veiculo_id')->nullable()->constrained('veiculos')->nullOnDelete();
            $table->foreignUuid('dispositivo_id')->nullable()->constrained('dispositivos_estudante')->nullOnDelete();
            $table->foreignId('presenca_id')->nullable()->constrained('presencas')->nullOnDelete();
            $table->string('etapa', 20);
            $table->string('resultado', 20);
            $table->string('codigo', 40)->nullable();
            $table->unsignedInteger('distancia_metros')->nullable();
            $table->unsignedInteger('precisao_metros')->nullable();
            $table->dateTime('recebida_em');
            // Pedido de revisao feito pelo estudante e a decisao da gestao.
            $table->string('revisao_status', 20)->nullable();
            $table->text('revisao_motivo')->nullable();
            $table->dateTime('revisao_solicitada_em')->nullable();
            $table->foreignId('revisao_analisada_por')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('revisao_analisada_em')->nullable();
            $table->text('revisao_parecer')->nullable();
            $table->timestamps();

            $table->index(['estudante_id', 'recebida_em']);
            $table->index(['codigo', 'recebida_em']);
            $table->index('revisao_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tentativas_presenca');
        Schema::dropIfExists('presencas');
    }
};
