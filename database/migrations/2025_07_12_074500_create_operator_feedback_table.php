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
        Schema::create('operator_feedback', function (Blueprint $table) {
            $table->id();
            $table->string('titolo')->comment('Titolo della richiesta operativa');
            $table->text('descrizione')->comment('Descrizione dettagliata della richiesta');
            $table->enum('status', ['pending', 'in_progress', 'done', 'rejected'])
                  ->default('pending')
                  ->comment('Stato della richiesta: pending, in_progress, done, rejected');
            $table->json('metadata')->nullable()
                  ->comment('Dati aggiuntivi JSON per estensioni future');
            $table->timestamps();
            
            // Indice per performance delle query API
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operator_feedback');
    }
};
