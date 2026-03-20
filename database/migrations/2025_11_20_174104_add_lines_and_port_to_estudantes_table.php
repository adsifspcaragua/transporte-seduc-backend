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
        Schema::table('estudantes', function (Blueprint $table) {
            $table->bigInteger('line_id')->after('days_of_week');;
            $table->string('port')->after('line_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('estudantes', function (Blueprint $table) {
            //
        });
    }
};
