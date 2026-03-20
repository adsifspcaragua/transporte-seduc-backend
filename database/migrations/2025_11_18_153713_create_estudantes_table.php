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
            $table->string('rg');
            $table->string('cpf')->unique();
            $table->date('birth_date');
            $table->string('phone');
            $table->string('address');
            $table->string('father_name');
            $table->string('mother_name');
            $table->string('course');
            $table->integer('semester');
            $table->string('year_completion');
            $table->string('instituicao_id');
            $table->time('start_time');
            $table->time('end_time');
            $table->json('days_of_week');
            $table->boolean('has_scholarship');
            $table->string('scholarship_type')->nullable();
            $table->string('observation');
            $table->string('status');
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
