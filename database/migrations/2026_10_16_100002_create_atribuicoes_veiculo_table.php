<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Qual van atende a linha em cada periodo.
     *
     * Quando mais de uma atribuicao cobre o mesmo dia, vale a de inicio mais
     * recente: a troca excepcional de um dia (inicio = fim) se sobrepoe a
     * atribuicao de longo prazo sem precisar encerra-la, e sem reimprimir QR.
     */
    public function up(): void
    {
        Schema::create('atribuicoes_veiculo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('veiculo_id')->constrained('veiculos')->cascadeOnDelete();
            $table->foreignId('linha_id')->constrained('linhas')->cascadeOnDelete();
            $table->date('inicio');
            $table->date('fim')->nullable();
            $table->string('motivo')->nullable();
            $table->foreignId('criada_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['linha_id', 'inicio']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('atribuicoes_veiculo');
    }
};
