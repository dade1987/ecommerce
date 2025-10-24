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
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->onDelete('cascade');
            $table->foreignId('inventory_movement_id')->nullable()->constrained('logistic_inventory_movements')->onDelete('set null');
            $table->foreignId('internal_product_id')->constrained('internal_products')->onDelete('cascade');
            $table->enum('item_type', ['physical', 'service', 'virtual'])->default('physical');
            $table->json('product_twin_ids')->nullable(); // Array di UUID per tracciabilità specifica
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_price', 10, 2);
            $table->text('description')->nullable();
            $table->timestamps();
            
            // Indici per performance
            $table->index(['invoice_id', 'item_type']);
            $table->index('internal_product_id');
            $table->index('inventory_movement_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};
