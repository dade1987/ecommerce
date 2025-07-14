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
        Schema::create('incoming_emails', function (Blueprint $table) {
            $table->id();
            $table->string('message_id')->unique();
            $table->foreignId('parent_id')->nullable()->constrained('incoming_emails')->onDelete('cascade');
            $table->string('subject')->nullable();
            $table->string('from_address');
            $table->text('to_address');
            $table->longText('body_html')->nullable();
            $table->longText('body_text')->nullable();
            $table->longText('summary')->nullable();
            $table->longText('translation')->nullable();
            $table->unsignedTinyInteger('priority')->nullable()->index();
            $table->boolean('is_read')->default(false)->index();
            $table->enum('type', ['inbox', 'sent'])->default('inbox');
            $table->timestamp('received_at')->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incoming_emails');
    }
};
