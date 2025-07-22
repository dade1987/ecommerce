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
        Schema::table('logistic_inventory_movements', function (Blueprint $table) {
            $table->decimal('distance_km', 8, 2)->nullable()->after('note');
            $table->string('transport_mode')->nullable()->after('distance_km');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('logistic_inventory_movements', function (Blueprint $table) {
            $table->dropColumn(['distance_km', 'transport_mode']);
        });
    }
};
