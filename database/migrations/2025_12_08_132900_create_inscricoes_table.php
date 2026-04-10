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
        Schema::create('inscricoes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('cpf')->unique();
            $table->string('rg')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->unique();
            $table->string('cep')->nullable();
            $table->string('address')->nullable();
            $table->string('neighborhood')->nullable();
            $table->string('city')->nullable();
            $table->string('number')->nullable();
            $table->string('father_name');
            $table->string('mother_name');
            $table->text('observation')->nullable();
            $table->string('status')->default('incompleto');
            $table->boolean('accepted_terms')->default(false);
            $table->boolean('accepted_terms_2')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inscricoes');
    }
};
