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
        Schema::create('invoice_item_product_twin', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_item_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_twin_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            // Indici per performance
            $table->index(['invoice_item_id', 'product_twin_id']);
            $table->unique(['invoice_item_id', 'product_twin_id']); // Previene duplicati
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_item_product_twin');
    }
};
