<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personnels', function (Blueprint $table) {
            if (!Schema::hasColumn('personnels', 'type_personnel')) {
                $table->string('type_personnel', 30)->default('permanent')->after('nationalite');
            }
        });

        Schema::table('programme_specialites', function (Blueprint $table) {
            if (!Schema::hasColumn('programme_specialites', 'volume_horaire')) {
                $table->decimal('volume_horaire', 8, 2)->default(0)->after('semestre');
            }
        });

        Schema::create('salles', function (Blueprint $table) {
            $table->id();
            $table->string('nom_salle');
            $table->string('code_salle')->nullable();
            $table->integer('capacite')->default(0);
            $table->string('statut')->default('actif');
            $table->integer('id_user')->default(0);
            $table->timestamps();
        });

        Schema::create('taux_horaires', function (Blueprint $table) {
            $table->id();
            $table->string('libelle');
            $table->decimal('montant', 12, 2)->default(0);
            $table->boolean('par_defaut')->default(false);
            $table->string('statut')->default('actif');
            $table->integer('id_user')->default(0);
            $table->timestamps();
        });

        Schema::create('plage_horaires', function (Blueprint $table) {
            $table->id();
            $table->string('libelle');
            $table->time('heure_debut');
            $table->time('heure_fin');
            $table->string('type_plage')->default('cours');
            $table->integer('ordre')->default(0);
            $table->string('statut')->default('actif');
            $table->integer('id_user')->default(0);
            $table->timestamps();
        });

        Schema::create('cours_enseignants', function (Blueprint $table) {
            $table->id();
            $table->integer('id_personnel')->index();
            $table->integer('id_programme_specialite')->index();
            $table->integer('id_taux_horaire')->nullable()->index();
            $table->integer('id_salle')->index();
            $table->integer('id_cycle')->index();
            $table->integer('id_filiere')->index();
            $table->integer('id_niveau')->index();
            $table->integer('id_specialite')->index();
            $table->integer('id_annee_academique')->index();
            $table->integer('id_entite')->index();
            $table->date('date_debut');
            $table->date('date_fin');
            $table->string('statut')->default('actif');
            $table->decimal('volume_horaire_prevu', 8, 2)->default(0);
            $table->integer('id_user')->default(0);
            $table->timestamps();
        });

        Schema::create('seance_cours', function (Blueprint $table) {
            $table->id();
            $table->integer('id_cours_enseignant')->index();
            $table->integer('id_plage_horaire')->index();
            $table->unsignedTinyInteger('jour_semaine');
            $table->date('date_seance')->nullable();
            $table->decimal('duree_heures', 8, 2)->default(0);
            $table->string('statut')->default('programme');
            $table->integer('id_user')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seance_cours');
        Schema::dropIfExists('cours_enseignants');
        Schema::dropIfExists('plage_horaires');
        Schema::dropIfExists('taux_horaires');
        Schema::dropIfExists('salles');

        Schema::table('programme_specialites', function (Blueprint $table) {
            if (Schema::hasColumn('programme_specialites', 'volume_horaire')) {
                $table->dropColumn('volume_horaire');
            }
        });

        Schema::table('personnels', function (Blueprint $table) {
            if (Schema::hasColumn('personnels', 'type_personnel')) {
                $table->dropColumn('type_personnel');
            }
        });
    }
};
