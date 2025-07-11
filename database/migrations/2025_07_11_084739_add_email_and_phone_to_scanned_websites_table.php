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
        Schema::table('scanned_websites', function (Blueprint $table) {
            $table->string('email')->after('domain');
            $table->string('phone_number')->after('email')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scanned_websites', function (Blueprint $table) {
            $table->dropColumn('email');
            $table->dropColumn('phone_number');
        });
    }
};
