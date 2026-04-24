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
        Schema::create('element_ligne_budgetaire_entrees', function (Blueprint $table) {
            $table->id();
            $table->string('libelle_elements_ligne_budgetaire_entree');
            $table->string('code_elements_ligne_budgetaire_entree');
            $table->string('numero_compte_elements_ligne_budgetaire_entree');
            $table->string('description');
            $table->date('date_creation');
            $table->integer('id_ligne_budgetaire_entree')->default(0);
            $table->integer('id_user')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('element_ligne_budgetaire_entrees');
    }
};
