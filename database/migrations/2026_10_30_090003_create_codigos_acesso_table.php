<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Codigos enviados por e-mail para provar que quem informou o CPF e o dono
     * do cadastro. Aqui fica so o HMAC do codigo, nunca o codigo.
     */
    public function up(): void
    {
        Schema::create('codigos_acesso', function (Blueprint $table) {
            $table->id();
            $table->char('cpf', 11);
            $table->char('codigo_hash', 64);
            $table->unsignedTinyInteger('tentativas')->default(0);
            $table->dateTime('expira_em');
            $table->dateTime('usado_em')->nullable();
            $table->timestamps();

            $table->index(['cpf', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('codigos_acesso');
    }
};
