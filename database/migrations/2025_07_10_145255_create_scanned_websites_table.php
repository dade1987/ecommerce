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
        Schema::create('scanned_websites', function (Blueprint $table) {
            $table->id();
            $table->string('domain');
            $table->integer('risk_percentage');
            $table->json('critical_points')->nullable();
            $table->json('raw_data')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamp('scanned_at');
            $table->timestamps();
            
            $table->index('domain');
            $table->index('risk_percentage');
            $table->index('scanned_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scanned_websites');
    }
};
