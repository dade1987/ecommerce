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
        Schema::table('product_twins', function (Blueprint $table) {
            $table->foreignId('current_warehouse_id')->nullable()->constrained('logistic_warehouses')->after('internal_product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_twins', function (Blueprint $table) {
            $table->dropForeign(['current_warehouse_id']);
            $table->dropColumn('current_warehouse_id');
        });
    }
};
