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
        // Vérifier si la colonne existe avant de la supprimer
        if (Schema::hasColumn('comptes', 'solde')) {
            Schema::table('comptes', function (Blueprint $table) {
                $table->dropColumn('solde');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comptes', function (Blueprint $table) {
            $table->decimal('solde', 15, 2)->default(0);
        });
    }
};
