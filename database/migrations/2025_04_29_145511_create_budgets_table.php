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
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->string('libelle_ligne_budget');
            $table->date('date_debut');
            $table->date('date_fin');
            $table->integer('id_user')->default(0);
            $table->date('date_creation');
            $table->double('montant_global')->default(0);
            $table->string('code_budget');
            $table->string('description');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budgets');
    }
};
