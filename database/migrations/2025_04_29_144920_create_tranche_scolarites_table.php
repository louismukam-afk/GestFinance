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
        Schema::create('tranche_scolarites', function (Blueprint $table) {
            $table->id();
            $table->string('nom_tranche');
            $table->integer('id_scolarite')->default(0);
            $table->integer('id_cycle')->default(0);
            $table->integer('id_filiere')->default(0);
            $table->integer('id_niveau')->default(0);
            $table->integer('id_specialite')->default(0);
            $table->date('date_limite');
            $table->double('montant_tranche')->default(0);
            $table->integer('id_user')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tranche_scolarites');
    }
};
