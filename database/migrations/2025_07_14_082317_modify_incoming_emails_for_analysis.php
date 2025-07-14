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
        Schema::table('incoming_emails', function (Blueprint $table) {
            $table->text('analysis')->nullable()->after('body_text');
            $table->dropColumn(['summary', 'translation']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incoming_emails', function (Blueprint $table) {
            $table->dropColumn('analysis');
            $table->longText('summary')->nullable();
            $table->longText('translation')->nullable();
        });
    }
};
