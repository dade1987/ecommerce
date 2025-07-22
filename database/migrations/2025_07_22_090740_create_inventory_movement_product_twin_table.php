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
        Schema::create('inventory_movement_product_twin', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_movement_id')->constrained('logistic_inventory_movements')->onDelete('cascade');
            $table->foreignId('product_twin_id')->constrained('product_twins')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_movement_product_twin');
    }
};
