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
        Schema::create('internal_products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->string('unit_of_measure');
            $table->decimal('weight', 8, 2)->nullable();
            $table->json('materials')->nullable();
            $table->decimal('emission_factor', 8, 4)->nullable();
            $table->decimal('co2_avoided', 8, 2)->nullable();
            $table->integer('expected_lifespan_days')->nullable();
            $table->boolean('is_recyclable')->default(false);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('internal_products');
    }
};
