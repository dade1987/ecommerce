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
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('team_id')->nullable();

        });

        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('team_id')->nullable();

        });

        Schema::table('addresses', function (Blueprint $table) {
            $table->unsignedBigInteger('team_id')->nullable();
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->unsignedBigInteger('team_id')->nullable();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('team_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('team_id');

        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('team_id');

        });

        Schema::table('addresses', function (Blueprint $table) {
            $table->dropColumn('team_id');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('team_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('team_id');
        });
    }
};
