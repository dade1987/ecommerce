<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->integer('featured_image_id')->nullable();
            $table->text('description');
            // $table->string('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
