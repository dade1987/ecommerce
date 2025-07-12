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
        Schema::create('logistic_inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('logistic_product_id')->constrained('logistic_products')->onDelete('cascade');
            $table->foreignId('from_warehouse_id')->nullable()->constrained('logistic_warehouses')->onDelete('cascade');
            $table->foreignId('to_warehouse_id')->nullable()->constrained('logistic_warehouses')->onDelete('cascade');
            $table->enum('movement_type', ['carico', 'scarico', 'trasferimento'])
                  ->comment('Tipo di movimento: carico, scarico, trasferimento');
            $table->integer('quantita')->comment('Quantità del movimento');
            $table->text('note')->nullable()->comment('Note aggiuntive sul movimento');
            
            // Campi per integrazione con POC di produzione
            $table->integer('production_order_id')->nullable()
                  ->comment('ID ordine di produzione per movimenti automatici');
            $table->boolean('origine_automatica')->default(false)
                  ->comment('Distingue movimenti generati automaticamente da produzione');
            
            $table->timestamps();
            
            // Indici per performance
            $table->index(['logistic_product_id', 'movement_type'], 'idx_product_movement_type');
            $table->index('production_order_id', 'idx_production_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logistic_inventory_movements');
    }
};
