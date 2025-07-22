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
        Schema::dropIfExists('logistic_products');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('logistic_products', function (Blueprint $table) {
            $table->id();
            $table->string('codice')->unique()->comment('Codice prodotto logistico univoco');
            $table->string('nome')->comment('Nome del prodotto');
            $table->text('descrizione')->nullable()->comment('Descrizione del prodotto');
            $table->string('unita_misura')->comment('Unità di misura (es. kg, pz, litri)');
            $table->timestamps();
        });
    }
};
