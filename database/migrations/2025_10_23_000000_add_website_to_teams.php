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
        Schema::table('teams', function (Blueprint $table) {
            // Cambia website singolo a websites (JSON array)
            if (Schema::hasColumn('teams', 'website')) {
                $table->dropColumn('website');
            }
            if (!Schema::hasColumn('teams', 'websites')) {
                $table->json('websites')->nullable()->after('phone');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            if (Schema::hasColumn('teams', 'websites')) {
                $table->dropColumn('websites');
            }
            $table->string('website')->nullable()->after('phone');
        });
    }
};
