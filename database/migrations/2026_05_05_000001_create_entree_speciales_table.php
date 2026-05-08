<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entree_speciales', function (Blueprint $table) {
            $table->id();
            $table->string('type_entree', 30)->default('dette');
            $table->string('code_entree')->nullable();
            $table->string('libelle');
            $table->string('nom_tiers');
            $table->string('telephone_tiers')->nullable();
            $table->string('adresse_tiers')->nullable();
            $table->date('date_entree');
            $table->date('date_contraction_dette')->nullable();
            $table->date('date_remboursement')->nullable();
            $table->boolean('remboursement_multiple')->default(false);
            $table->integer('nombre_echeances')->default(0);
            $table->double('montant')->default(0);
            $table->integer('id_caisse')->default(0);
            $table->integer('id_budget')->default(0);
            $table->integer('id_annee_academique')->default(0);
            $table->text('observations')->nullable();
            $table->string('statut', 30)->default('actif');
            $table->integer('id_user')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entree_speciales');
    }
};
