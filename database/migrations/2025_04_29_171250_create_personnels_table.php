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
        Schema::create('personnels', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->date('date_naissance');
            $table->string('lieu_naissance');
            $table->string('adresse');
            $table->string('sexe');
            $table->string('statut_matrimonial');
            $table->string('email');
            $table->string('telephone');
            $table->string('telephone_whatsapp');
            $table->string('diplome');
            $table->string('niveau_etude');
            $table->string('domaine_formation');
            $table->date('date_recrutement');
            $table->integer('id_user');
            $table->string('nationalite');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personnels');
    }
};
