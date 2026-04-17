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
        Schema::create('inscricao_instituicoes', function (Blueprint $table) {
            $table->id();
            $table->string('course');
            $table->string('semester');
            $table->date('expected_completion'); // DATA
            $table->foreignId('instituicao_id')->constrained('instituicoes');
            $table->integer('shift'); // MATUTINO / NOTURNO
            $table->string('city_destination');
            $table->boolean('used_transport');
            $table->json('days_of_week');
            $table->boolean('has_scholarship');
            $table->string('scholarship_type')->nullable();
            $table->timestamps();
            $table->foreignId('inscricao_id')->references('id')->on('inscricoes')->unique()->cascadeDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inscricao_instituicoes');
    }
};
