<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('etudiants', function (Blueprint $table) {
            $table->id();

            $table->string('telephone_whatsapp')->nullable();
            $table->date('date_naissance')->nullable();
            $table->string('lieu_naissance')->nullable();
            $table->string('sexe')->nullable();
            $table->string('email')->nullable();
            $table->string('adresse')->nullable();
            $table->string('departement_origine')->nullable();
            $table->string('region_origine')->nullable();
            $table->string('nom_pere')->nullable();
            $table->string('telephone_whatsapp_pere')->nullable();
            $table->string('nom_mere')->nullable();
            $table->string('nom_tuteur')->nullable();
            $table->string('telephone_tuteur')->nullable();
            $table->string('matricule')->nullable();
            $table->string('telephone_2_etudiants')->nullable();
            $table->string('adresse_tuteur')->nullable();
            $table->string('photo')->nullable();
            $table->string('dernier_etablissement_frequente')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('etudiants');
    }
};