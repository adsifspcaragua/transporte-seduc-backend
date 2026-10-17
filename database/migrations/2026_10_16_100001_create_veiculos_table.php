<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A van fisica, identificada pelo QR Code fixo afixado nela.
     *
     * O QR carrega um token opaco e aleatorio; aqui fica so o hash dele. O
     * token aparece uma unica vez, ao gerar ou rotacionar, para ser impresso.
     * Perdeu a arte? Rotaciona e reimprime: o token antigo deixa de valer.
     */
    public function up(): void
    {
        Schema::create('veiculos', function (Blueprint $table) {
            $table->id();
            $table->string('identificacao')->unique();
            $table->string('placa', 10)->nullable()->unique();
            $table->boolean('ativo')->default(true);
            $table->char('qr_token_hash', 64)->nullable()->unique();
            $table->timestamp('qr_gerado_em')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('veiculos');
    }
};
