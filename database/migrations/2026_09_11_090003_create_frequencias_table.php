<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A linha de cada estudante dentro da chamada do dia.
     *
     * As linhas nascem junto com a chamada, uma para cada estudante esperado
     * naquele dia (ativo, na linha e com o dia na sua grade). Isso congela a
     * lista de quem era esperado: se depois o estudante trocar de linha ou for
     * inativado, a folha daquele dia continua contando a historia certa.
     *
     * Por isso existe a situacao "Pendente": e o estudante que estava na lista
     * e ainda nao foi marcado. Sem ela, uma folha intocada afirmaria que todos
     * estavam presentes.
     */
    public function up(): void
    {
        Schema::create('frequencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chamada_id')->constrained('chamadas')->cascadeOnDelete();
            $table->foreignId('estudante_id')->constrained('estudantes')->cascadeOnDelete();
            $table->string('situacao')->default('Pendente');
            // Justificativa da falta, ou qualquer nota do motorista sobre o
            // embarque daquele estudante.
            $table->string('observacao')->nullable();
            $table->foreignId('marcada_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('marcada_em')->nullable();
            $table->timestamps();

            $table->unique(['chamada_id', 'estudante_id']);
            $table->index(['estudante_id', 'situacao']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('frequencias');
    }
};
