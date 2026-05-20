<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('emploi_permanents', function (Blueprint $table) {
            $table->id();
            $table->integer('id_personnel')->index();
            $table->integer('id_plage_horaire')->index();
            $table->integer('id_annee_academique')->nullable()->index();
            $table->integer('id_entite')->nullable()->index();
            $table->unsignedTinyInteger('jour_semaine')->index();
            $table->date('date_debut');
            $table->date('date_fin')->nullable();
            $table->string('statut')->default('actif');
            $table->text('observations')->nullable();
            $table->integer('id_user')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emploi_permanents');
    }
};
