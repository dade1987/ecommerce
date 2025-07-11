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
        Schema::create('production_phases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_order_id')->constrained('production_orders')->cascadeOnDelete();
            $table->string('name')->comment('Fase');
            $table->dateTime('start_time')->nullable()->comment('Inizio lavorazione');
            $table->dateTime('end_time')->nullable()->comment('Fine lavorazione');
            $table->string('operator')->nullable()->comment('Operatore');
            $table->boolean('is_completed')->default(false)->comment('Fase completata');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_phases');
    }
};
