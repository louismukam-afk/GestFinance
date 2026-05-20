<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discipline_personnels', function (Blueprint $table) {
            $table->id();
            $table->integer('id_personnel')->index();
            $table->integer('id_cours_enseignant')->nullable()->index();
            $table->integer('id_seance_cours')->nullable()->index();
            $table->foreignId('id_biometrie_import')->nullable()->constrained('biometrie_imports')->nullOnDelete();
            $table->integer('id_annee_academique')->nullable()->index();
            $table->integer('id_entite')->nullable()->index();
            $table->string('type_discipline', 30)->index();
            $table->date('date_discipline')->index();
            $table->decimal('duree_heures', 8, 2)->default(0);
            $table->integer('minutes_retard')->default(0);
            $table->text('motif')->nullable();
            $table->string('statut', 30)->default('non_justifie');
            $table->date('date_justification')->nullable();
            $table->text('motif_justification')->nullable();
            $table->json('preuves')->nullable();
            $table->integer('id_user')->default(0);
            $table->timestamps();

            $table->unique(['id_personnel', 'id_seance_cours', 'id_biometrie_import', 'date_discipline', 'type_discipline'], 'uniq_discipline_auto');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discipline_personnels');
    }
};
