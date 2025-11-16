<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The database connection that should be used by the migration.
     */
    protected $connection = 'webscraper';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection($this->connection)->create('search_result_cache', function (Blueprint $table) {
            $table->id();
            $table->text('site_url');
            $table->string('query_hash', 32);
            $table->text('original_query');
            $table->text('query_embedding')->nullable(); // JSON encoded embedding vector for similarity search
            $table->json('results_json');
            $table->text('ai_analysis')->nullable();
            $table->integer('pages_visited')->default(0);
            $table->timestamp('created_at');
            $table->timestamp('expires_at');

            // Unique constraint on site_url + query_hash
            $table->unique(['site_url', 'query_hash'], 'unique_site_query');

            // Indexes for performance
            $table->index(['site_url', 'query_hash', 'expires_at'], 'idx_search_cache_lookup');
            $table->index('expires_at', 'idx_search_cache_expiry');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('search_result_cache');
    }
};