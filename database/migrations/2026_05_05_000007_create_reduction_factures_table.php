<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reduction_factures', function (Blueprint $table) {
            $table->id();
            $table->integer('id_facture_etudiant')->default(0)->index();
            $table->integer('id_etudiant')->default(0)->index();
            $table->integer('id_entite')->default(0)->index();
            $table->integer('id_specialite')->default(0)->index();
            $table->integer('id_annee_academique')->default(0)->index();
            $table->integer('id_budget')->default(0)->index();
            $table->double('montant')->default(0);
            $table->string('motif')->nullable();
            $table->date('date_reduction');
            $table->text('observations')->nullable();
            $table->integer('id_user')->default(0)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reduction_factures');
    }
};
