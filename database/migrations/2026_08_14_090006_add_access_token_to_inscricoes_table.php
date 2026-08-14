<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Credencial da inscrição na lista de espera.
     *
     * O estudante recebe o token ao criar a inscrição e o usa para consultar e
     * corrigir os próprios dados enquanto ela não é analisada. Sem ele, o ID
     * sequencial daria acesso aos dados pessoais de qualquer inscrito.
     *
     * O token não expira: como não há login nem e-mail de recuperação, expirá-lo
     * deixaria o estudante sem acesso à própria inscrição.
     */
    public function up(): void
    {
        Schema::table('inscricoes', function (Blueprint $table) {
            $table->string('access_token', 64)->nullable()->unique()->after('accepted_terms_2');
        });

        DB::table('inscricoes')->whereNull('access_token')->orderBy('id')->each(function ($inscricao) {
            DB::table('inscricoes')->where('id', $inscricao->id)->update(['access_token' => Str::random(64)]);
        });
    }

    public function down(): void
    {
        Schema::table('inscricoes', function (Blueprint $table) {
            $table->dropUnique(['access_token']);
            $table->dropColumn('access_token');
        });
    }
};
