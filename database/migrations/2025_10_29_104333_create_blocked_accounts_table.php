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
        Schema::connection('neon')->create('blocked_accounts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('compte_id');
            $table->string('numero_compte', 10);
            $table->string('type_compte');
            $table->uuid('client_id');
            $table->timestamp('date_debut_blocage');
            $table->timestamp('date_fin_blocage')->nullable();
            $table->text('motif');
            $table->json('compte_data'); // Données complètes du compte
            $table->json('client_data'); // Données du client
            $table->timestamp('archived_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blocked_accounts');
    }
};
