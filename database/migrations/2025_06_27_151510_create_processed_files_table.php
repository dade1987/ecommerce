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
        Schema::create('processed_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('extractor_id')->constrained()->cascadeOnDelete();
            $table->string('original_filename')->nullable();
            $table->string('file_path');
            $table->string('mime_type');
            $table->json('gemini_response')->nullable();
            $table->string('status')->default('processing');
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('processed_files');
    }
};
