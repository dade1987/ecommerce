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
        Schema::table('logistic_warehouses', function (Blueprint $table) {
            $table->renameColumn('nome', 'name');
            $table->renameColumn('tipo', 'type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('logistic_warehouses', function (Blueprint $table) {
            $table->renameColumn('name', 'nome');
            $table->renameColumn('type', 'tipo');
        });
    }
};
