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
        Schema::create('notification_reads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('notification_type'); // 'intervention', 'quote', 'new_quote'
            $table->unsignedBigInteger('notification_id'); // ID de l'intervention/devis
            $table->timestamp('read_at');
            $table->timestamps();

            // Index unique pour éviter les doublons et optimiser les requêtes
            $table->unique(['user_id', 'notification_type', 'notification_id'], 'notif_read_uid_type_nid_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_reads');
    }
};
