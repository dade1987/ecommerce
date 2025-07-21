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
        Schema::table('workstations', function (Blueprint $table) {
            $table->string('real_time_status')->default('inattiva')->after('status');
            $table->float('wear_level')->default(0)->after('real_time_status');
            $table->timestamp('last_maintenance_date')->nullable()->after('wear_level');
            $table->float('error_rate')->default(0)->after('last_maintenance_date');
            $table->float('current_speed')->nullable()->after('error_rate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workstations', function (Blueprint $table) {
            $table->dropColumn([
                'real_time_status',
                'wear_level',
                'last_maintenance_date',
                'error_rate',
                'current_speed',
            ]);
        });
    }
};
