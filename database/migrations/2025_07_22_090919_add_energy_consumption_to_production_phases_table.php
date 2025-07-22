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
            $table->decimal('energy_consumption', 8, 2)->nullable()->after('is_maintenance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('production_phases', function (Blueprint $table) {
            $table->dropColumn('energy_consumption');
        });
    }
};
