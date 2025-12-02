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
        Schema::create('website_searches', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('website')->nullable()->comment('URL del sito scrapato');
            $table->text('query')->comment('Query di ricerca effettuata');
            $table->foreignId('team_id')->nullable()->constrained('teams')->onDelete('set null');
            $table->string('locale', 10)->default('it');
            $table->text('response')->nullable()->comment('Risposta generata');
            $table->integer('content_length')->nullable()->comment('Lunghezza contenuto scrapato');
            $table->boolean('from_cache')->default(false)->comment('Se il contenuto era in cache');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('website_searches');
    }
};
