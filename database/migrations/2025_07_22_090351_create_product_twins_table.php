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
        Schema::create('product_twins', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->foreignId('internal_product_id')->constrained('internal_products')->onDelete('cascade');
            $table->string('lifecycle_status')->default('in_production');
            $table->decimal('co2_emissions_production', 8, 2)->nullable();
            $table->decimal('co2_emissions_logistics', 8, 2)->nullable();
            $table->decimal('co2_emissions_total', 8, 2)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_twins');
    }
};
