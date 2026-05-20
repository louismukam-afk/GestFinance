<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personnel_entites', function (Blueprint $table) {
            $table->id();
            $table->integer('id_personnel')->index();
            $table->integer('id_entite')->index();
            $table->integer('id_annee_academique')->nullable()->index();
            $table->date('date_debut')->nullable();
            $table->date('date_fin')->nullable();
            $table->string('statut')->default('actif');
            $table->integer('id_user')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personnel_entites');
    }
};
