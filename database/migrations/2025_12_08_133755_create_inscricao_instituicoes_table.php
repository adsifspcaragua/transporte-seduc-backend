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
            $table->string('course')->nullable();;
            $table->string('semester')->nullable();;
            $table->date('expected_completion')->nullable();; // DATA
            $table->foreignId('instituicao_id')->constrained('instituicoes')->nullable();;
            $table->integer('shift')->nullable();; // MATUTINO / NOTURNO
            $table->string('city_destination')->nullable();;
            $table->boolean('used_transport')->default(false)->nullable();;
            $table->json('days_of_week')->nullable();;
            $table->boolean('has_scholarship')->nullable();;
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
