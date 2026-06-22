<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('estudantes', 'start_time') && ! Schema::hasColumn('estudantes', 'end_time')) {
            return;
        }

        Schema::table('estudantes', function (Blueprint $table) {
            if (Schema::hasColumn('estudantes', 'start_time')) {
                $table->dropColumn('start_time');
            }

            if (Schema::hasColumn('estudantes', 'end_time')) {
                $table->dropColumn('end_time');
            }
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('estudantes', 'start_time') || Schema::hasColumn('estudantes', 'end_time')) {
            return;
        }

        Schema::table('estudantes', function (Blueprint $table) {
            $table->time('start_time')->nullable()->after('address');
            $table->time('end_time')->nullable()->after('start_time');
        });
    }
};
