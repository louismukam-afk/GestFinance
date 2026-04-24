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
        Schema::create('donnee_budgetaire_sorties', function (Blueprint $table) {
            $table->id();
            $table->string('donnee_ligne_budgetaire_sortie');
            $table->string('code_donnee_budgetaire_sortie');
            $table->string('numero_donnee_budgetaire_sortie');
            $table->string('description');
            $table->date('date_creation');
            $table->integer('id_ligne_budgetaire_sortie')->default(0);
            $table->integer('id_budget')->default(0);
            $table->double('montant')->default(0);
            $table->integer('id_user')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('donnee_budgetaire_sorties');
    }
};
