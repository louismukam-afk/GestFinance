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
        Schema::create('reglement_etudiants', function (Blueprint $table) {
            $table->id();
            $table->integer('id_cycle')->default(0);
            $table->integer('id_niveau')->default(0);
            $table->integer('id_caisse')->default(0);
            $table->integer('id_banque')->default(0);
            $table->integer('id_filiere')->default(0);
            $table->integer('id_scolarite')->default(0);
            $table->integer('id_frais')->default(0);
            $table->integer('id_tranche_scolarite')->default(0);
            $table->integer('id_specialite')->default(0);
            $table->integer('id_etudiant')->default(0);
            $table->integer('id_budget')->default(0);
            $table->integer('id_ligne_budgetaire_entree')->default(0);
            $table->integer('id_element_ligne_budgetaire_entree')->default(0);
            $table->integer('id_donnee_ligne_budgetaire_entree')->default(0);
            $table->integer('id_donnee_budgetaire_entree')->default(0);
            $table->integer('id_entite')->default(0);
            $table->double('montant_reglement')->default(0);
            $table->string('motif_reglement');
            $table->string('lettre');
            $table->double('reste_reglement')->default(0);
            $table->integer('numero_reglement');
            $table->date('date_reglement');
            $table->integer('id_annee_academique')->default(0);
            $table->integer('type_reglement')->default(0);
            $table->string('type_versement');
            $table->integer('id_user')->default(0);
            $table->integer('id_facture_etudiant')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reglement_etudiants');
    }
};
