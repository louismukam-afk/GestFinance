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
        Schema::create('bon_commandeoks', function (Blueprint $table) {
            $table->id();
            $table->string('nom_bon_commande');
            $table->string('description_bon_commande');
            $table->date('date_debut');
            $table->date('date_fin');
            $table->date('date_entree_signature');
            $table->date('date_validation')->nullable()->change();
            $table->double('montant_total')->default(0);
            $table->double('montant_realise')->default(0)->nullable()->change();
            $table->double('reste')->default(0);;
            $table->string('montant_lettre');
            $table->integer('id_personnel')->default(0);
            $table->integer('id_user')->default(0);
            $table->integer('statuts')->default(0);
            $table->integer('id_entite')->default(0);
            $table->integer('validation_pdg')->default(0);
            $table->integer('validation_daf')->default(0);
            $table->integer('validation_achats')->default(0);
            $table->integer('validation_emetteur')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bon_commandeoks');
    }
};
