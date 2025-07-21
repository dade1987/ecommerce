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
            $table->foreignId('workstation_id')->nullable()->after('production_order_id')->constrained('workstations')->nullOnDelete();
            $table->unsignedInteger('estimated_duration')->nullable()->comment('in minutes')->after('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('production_phases', function (Blueprint $table) {
            $table->dropForeign(['workstation_id']);
            $table->dropColumn(['workstation_id', 'estimated_duration']);
        });
    }
};
