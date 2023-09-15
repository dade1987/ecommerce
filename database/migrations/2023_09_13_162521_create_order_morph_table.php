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
        Schema::create('order_morph', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->nullableMorphs('model');
            $table->integer('order_id')->index();
            $table->integer('user_id')->nullable()->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('address_morph');
    }
};
