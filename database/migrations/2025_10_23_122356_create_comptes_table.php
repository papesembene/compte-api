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
         Schema::create('comptes', function (Blueprint $table) {
             $table->uuid('id')->primary();
             $table->string('numero_compte', 10)->unique();
             $table->string('type_compte'); // e.g., 'courant', 'epargne'
             $table->string('statut')->default('debloque'); // 'bloque' or 'debloque'
             $table->uuid('client_id');
             $table->timestamps();
             $table->softDeletes();

             // Foreign key
             $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');

             // Indexes
             $table->index('numero_compte');
             $table->index('client_id');
             $table->index('statut');
             $table->index('type_compte');
         });
     }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comptes');
    }
};
