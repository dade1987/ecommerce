<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Aggiunge indici per velocizzare le query critiche nel RealtimeChatController
     */
    public function up(): void
    {
        // Indice su Team.slug per lookup rapido
        if (Schema::hasTable('teams') && !$this->indexExists('teams', 'teams_slug_index')) {
            Schema::table('teams', function (Blueprint $table) {
                $table->index('slug');
            });
        }

        // Indice su Product.name per LIKE query veloce
        if (Schema::hasTable('products') && !$this->indexExists('products', 'products_name_index')) {
            Schema::table('products', function (Blueprint $table) {
                $table->index('name');
            });
        }

        // Indice su Faq.team_id per query filtrate per team
        if (Schema::hasTable('faqs') && !$this->indexExists('faqs', 'faqs_team_id_index')) {
            Schema::table('faqs', function (Blueprint $table) {
                $table->index('team_id');
            });
        }

        // Indice composito su Faq(team_id, active) per ottimizzare filtri
        if (Schema::hasTable('faqs') && !$this->indexExists('faqs', 'faqs_team_id_active_index')) {
            Schema::table('faqs', function (Blueprint $table) {
                $table->index(['team_id', 'active']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('teams')) {
            Schema::table('teams', function (Blueprint $table) {
                $table->dropIndex('teams_slug_index');
            });
        }

        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropIndex('products_name_index');
            });
        }

        if (Schema::hasTable('faqs')) {
            Schema::table('faqs', function (Blueprint $table) {
                $table->dropIndex('faqs_team_id_index');
                $table->dropIndex('faqs_team_id_active_index');
            });
        }
    }

    /**
     * Helper per verificare se un indice esiste già
     */
    private function indexExists(string $table, string $indexName): bool
    {
        $indexes = Schema::getConnection()->getDoctrineSchemaManager()->listTableIndexes($table);
        return isset($indexes[$indexName]);
    }
};
