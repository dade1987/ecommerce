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
            $table->foreignId('internal_product_id')->nullable()->constrained('internal_products')->after('logistic_product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('logistic_inventory_movements', function (Blueprint $table) {
            $table->dropForeign(['internal_product_id']);
            $table->dropColumn('internal_product_id');
        });
    }
};
