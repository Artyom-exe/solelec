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
        Schema::create('interventions', function (Blueprint $table) {
            $table->id();
            $table->string('status')->default('en_attente'); // en_attente, en_cours, terminé
            $table->date('date');
            $table->text('notes')->nullable();
            $table->foreignId('clients_id')->constrained('clients')->onDelete('cascade');
            $table->foreignId('devis_id')->nullable()->constrained('quotes')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interventions');
    }
};
