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
        Schema::table('production_phases', function (Blueprint $table) {
            $table->dateTime('scheduled_start_time')->nullable()->after('end_time');
            $table->dateTime('scheduled_end_time')->nullable()->after('scheduled_start_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('production_phases', function (Blueprint $table) {
            $table->dropColumn(['scheduled_start_time', 'scheduled_end_time']);
        });
    }
};
