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
        Schema::create('team_morph', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->nullableMorphs('model');
            $table->integer('team_id')->index();
            $table->integer('user_id')->nullable()->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('team_morph');
    }
};
