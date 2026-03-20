<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('solicitacaos', function (Blueprint $table) {
            $table->json('pendencias')->nullable()->after('observacao');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('solicitacaos', function (Blueprint $table) {
            $table->dropColumn('pendencias');
        });
    }
};
