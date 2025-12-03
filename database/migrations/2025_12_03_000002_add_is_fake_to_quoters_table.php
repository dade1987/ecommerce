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
        Schema::table('quoters', function (Blueprint $table): void {
            $table->boolean('is_fake')
                ->default(false)
                ->after('content')
                ->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quoters', function (Blueprint $table): void {
            $table->dropColumn('is_fake');
        });
    }
};


