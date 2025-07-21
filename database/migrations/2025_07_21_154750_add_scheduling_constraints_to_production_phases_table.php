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
            $table->integer('setup_time')->default(0)->after('estimated_duration')->comment('Tempo di setup in minuti');
            $table->boolean('is_maintenance')->default(false)->after('is_completed')->comment('Indica se è una fase di manutenzione');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('production_phases', function (Blueprint $table) {
            $table->dropColumn(['setup_time', 'is_maintenance']);
        });
    }
};
