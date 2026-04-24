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
        Schema::create('element_bon_commandes', function (Blueprint $table) {
            $table->id();
            $table->string('nom_element_bon_commande');
            $table->string('description_elements_bon_commande');
            $table->integer('quantite_element_bon_commande');
            $table->integer('id_user')->default(0);
            $table->integer('id_bon_commande')->default(0);
            $table->float('prix_unitaire_element_bon_commande');
            $table->double('montant_total_element_bon_commande');
            $table->date('date_realisation');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('element_bon_commandes');
    }
};
