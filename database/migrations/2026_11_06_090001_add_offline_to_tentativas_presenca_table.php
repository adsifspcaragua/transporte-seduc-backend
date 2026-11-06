<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Comprovantes de presenca gravados sem conexao e enviados depois.
     *
     * Viram tentativas ja em revisao: `comprovante_id` e o id que o celular deu
     * ao comprovante (reenviar o mesmo nao duplica) e `capturado_em` e o horario
     * do celular no momento da leitura, dado auxiliar para a gestao decidir.
     */
    public function up(): void
    {
        Schema::table('tentativas_presenca', function (Blueprint $table) {
            $table->uuid('comprovante_id')->nullable()->after('presenca_id');
            $table->dateTime('capturado_em')->nullable()->after('comprovante_id');

            $table->unique(['estudante_id', 'comprovante_id']);
        });
    }

    public function down(): void
    {
        Schema::table('tentativas_presenca', function (Blueprint $table) {
            $table->dropUnique(['estudante_id', 'comprovante_id']);
            $table->dropColumn(['comprovante_id', 'capturado_em']);
        });
    }
};
