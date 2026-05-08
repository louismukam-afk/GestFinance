<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facture_rattrapage_lignes', function (Blueprint $table) {
            $table->id();
            $table->integer('id_facture_etudiant')->index();
            $table->integer('id_matiere')->index();
            $table->double('prix_unitaire')->default(0);
            $table->integer('quantite')->default(1);
            $table->double('montant')->default(0);
            $table->text('observation')->nullable();
            $table->integer('id_user')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facture_rattrapage_lignes');
    }
};
