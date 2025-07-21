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
            $table->unsignedInteger('batch_size')->default(1)->after('capacity');
            $table->unsignedInteger('time_per_unit')->default(10)->after('batch_size')->comment('Tempo in minuti per lavorare una singola unità');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workstations', function (Blueprint $table) {
            $table->dropColumn(['batch_size', 'time_per_unit']);
        });
    }
};
