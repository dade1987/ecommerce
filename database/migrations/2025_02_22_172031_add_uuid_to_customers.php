<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Step 1: Aggiungi la colonna uuid come nullable (senza unique)
        Schema::table('customers', function (Blueprint $table) {
            $table->uuid('uuid')->nullable();
        });

        // Step 2: Imposta gli UUID iniziali per i record esistenti
        DB::statement('UPDATE customers SET uuid = UUID() WHERE uuid IS NULL');

        // Step 3: Rendi la colonna non nullable
        Schema::table('customers', function (Blueprint $table) {
            $table->uuid('uuid')->nullable(false)->change();
        });

        // Step 4: Aggiungi l'indice univoco separatamente
        Schema::table('customers', function (Blueprint $table) {
            $table->unique('uuid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropUnique(['uuid']); // Rimuove l'indice univoco
            $table->dropColumn('uuid');
        });
    }
};
