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
         Schema::create('clients', function (Blueprint $table) {
             $table->id();
             $table->string('titulaire');
             $table->string('nci')->unique();
             $table->string('email')->unique();
             $table->string('telephone')->unique();
             $table->text('adresse');
             $table->timestamps();

             // Add indexes
             $table->index('nci');
             $table->index('email');
             $table->index('telephone');
         });
     }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
