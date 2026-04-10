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
        Schema::create('estudantes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('cpf')->unique();
            $table->date('birth_date');
            $table->string('phone');
            $table->string('address');
            $table->time('start_time');
            $table->time('end_time');
            $table->json('days_of_week');
            $table->string('observation')->nullable();
            $table->string('status');
            $table->foreignId('instituicao_id')->constrained('instituicoes');
            $table->unsignedInteger('linha_id')->nullable();
            $table->unsignedInteger('user_id')->nullable();
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estudantes');
    }
};
