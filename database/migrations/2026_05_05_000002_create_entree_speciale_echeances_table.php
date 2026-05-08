<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entree_speciale_echeances', function (Blueprint $table) {
            $table->id();
            $table->integer('id_entree_speciale')->default(0);
            $table->string('nom_echeance');
            $table->date('date_echeance');
            $table->double('montant')->nullable();
            $table->string('statut', 30)->default('en_attente');
            $table->date('date_paiement')->nullable();
            $table->text('observations')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entree_speciale_echeances');
    }
};
