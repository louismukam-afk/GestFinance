<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('retour_caisses', function (Blueprint $table) {
            $table->id();
            $table->integer('id_bon_commande')->default(0);
            $table->unsignedBigInteger('id_decaissement')->nullable();
            $table->integer('id_caisse')->default(0);
            $table->integer('id_budget')->default(0);
            $table->integer('id_ligne_budgetaire_sortie')->default(0);
            $table->integer('id_elements_ligne_budgetaire_sortie')->default(0);
            $table->integer('id_donnee_ligne_budgetaire_sortie')->default(0);
            $table->integer('id_donnee_budgetaire_sortie')->default(0);
            $table->integer('id_annee_academique')->default(0);
            $table->integer('id_user')->default(0);
            $table->string('numero_retour')->nullable();
            $table->string('motif')->nullable();
            $table->date('date_retour');
            $table->double('montant')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('retour_caisses');
    }
};
