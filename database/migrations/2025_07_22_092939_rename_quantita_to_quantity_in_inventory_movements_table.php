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
            $table->renameColumn('quantita', 'quantity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('logistic_inventory_movements', function (Blueprint $table) {
            $table->renameColumn('quantity', 'quantita');
        });
    }
};
