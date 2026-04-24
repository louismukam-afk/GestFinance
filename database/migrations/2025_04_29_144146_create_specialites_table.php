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
        Schema::create('specialites', function (Blueprint $table) {
            $table->id();
            $table->string('nom_specialite');
            $table->string('code_specialite');
            $table->integer('id_filiere')->default(0);
            $table->integer('capacite')->default(0);
            $table->integer('id_user')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('specialites');
    }
};
