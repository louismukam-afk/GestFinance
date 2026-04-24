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
        Schema::create('decaissements', function (Blueprint $table) {
            $table->id();
            $table->integer('id_ligne_budgetaire_sortie')->default(0);
            $table->integer('id_elements_ligne_budgetaire_sortie')->default(0);
            $table->integer('id_donnee_ligne_budgetaire_sortie')->default(0);
            $table->integer('id_donnee_budgetaire_sortie')->default(0);
            $table->integer('id_caisse')->default(0);
            $table->integer('id_banque')->default(0);
            $table->integer('id_bon_commande')->default(0);
            $table->string('numero_depense');
            $table->string('motif');
            $table->date('date_depense');
            $table->integer('id_budget')->default(0);
            $table->double('montant')->default(0);
            $table->integer('id_user')->default(0);
            $table->integer('id_personnel')->default(0);
            $table->integer('id_annee_academique')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('decaissements');
    }
};
