<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('biometrie_imports', function (Blueprint $table) {
            $table->id();
            $table->string('libelle')->nullable();
            $table->date('date_debut');
            $table->date('date_fin');
            $table->string('fichier')->nullable();
            $table->string('statut')->default('importe');
            $table->integer('total_lignes')->default(0);
            $table->integer('total_consolidees')->default(0);
            $table->integer('total_non_associees')->default(0);
            $table->text('observations')->nullable();
            $table->integer('id_user')->default(0);
            $table->timestamps();
        });

        Schema::create('biometrie_pointages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_biometrie_import')->constrained('biometrie_imports')->cascadeOnDelete();
            $table->integer('id_personnel')->nullable()->index();
            $table->string('departement')->nullable();
            $table->string('nom_biometrie')->nullable()->index();
            $table->string('numero_biometrie')->nullable()->index();
            $table->dateTime('date_heure_pointage')->index();
            $table->string('location_id')->nullable();
            $table->string('id_number')->nullable();
            $table->string('verify_code')->nullable();
            $table->string('card_no')->nullable();
            $table->json('raw_data')->nullable();
            $table->integer('id_user')->default(0);
            $table->timestamps();
        });

        Schema::create('heures_realisees_enseignants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_biometrie_import')->nullable()->constrained('biometrie_imports')->nullOnDelete();
            $table->integer('id_cours_enseignant')->index();
            $table->integer('id_seance_cours')->index();
            $table->integer('id_personnel')->index();
            $table->integer('id_programme_specialite')->index();
            $table->integer('id_taux_horaire')->nullable()->index();
            $table->integer('id_salle')->nullable()->index();
            $table->integer('id_cycle')->index();
            $table->integer('id_filiere')->index();
            $table->integer('id_niveau')->index();
            $table->integer('id_specialite')->index();
            $table->integer('id_annee_academique')->index();
            $table->integer('id_entite')->index();
            $table->date('date_seance')->index();
            $table->unsignedTinyInteger('jour_semaine');
            $table->integer('id_plage_horaire')->index();
            $table->time('heure_debut_prevue');
            $table->time('heure_fin_prevue');
            $table->time('heure_debut_reelle')->nullable();
            $table->time('heure_fin_reelle')->nullable();
            $table->decimal('duree_prevue', 8, 2)->default(0);
            $table->decimal('duree_realisee', 8, 2)->default(0);
            $table->decimal('montant_taux', 12, 2)->default(0);
            $table->decimal('montant_total', 12, 2)->default(0);
            $table->string('statut')->default('non_realise');
            $table->text('observation')->nullable();
            $table->integer('id_user')->default(0);
            $table->timestamps();

            $table->unique(['id_biometrie_import', 'id_seance_cours', 'date_seance'], 'uniq_bio_seance_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('heures_realisees_enseignants');
        Schema::dropIfExists('biometrie_pointages');
        Schema::dropIfExists('biometrie_imports');
    }
};
