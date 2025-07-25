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
        Schema::table('extractors', function (Blueprint $table) {
            $table->string('export_format')->default('json')->after('prompt');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('extractors', function (Blueprint $table) {
            $table->dropColumn('export_format');
        });
    }
};
