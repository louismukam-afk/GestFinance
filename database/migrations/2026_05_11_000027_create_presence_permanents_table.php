<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('biometrie_imports', function (Blueprint $table) {
            if (!Schema::hasColumn('biometrie_imports', 'type_import')) {
                $table->string('type_import', 30)->default('cours')->after('statut')->index();
            }
        });

        Schema::create('presence_permanents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_biometrie_import')->nullable()->constrained('biometrie_imports')->nullOnDelete();
            $table->integer('id_personnel')->index();
            $table->integer('id_emploi_permanent')->index();
            $table->integer('id_plage_horaire')->nullable()->index();
            $table->integer('id_annee_academique')->nullable()->index();
            $table->integer('id_entite')->nullable()->index();
            $table->date('date_presence')->index();
            $table->unsignedTinyInteger('jour_semaine');
            $table->time('heure_debut_prevue')->nullable();
            $table->time('heure_fin_prevue')->nullable();
            $table->time('heure_debut_reelle')->nullable();
            $table->time('heure_fin_reelle')->nullable();
            $table->decimal('duree_prevue', 8, 2)->default(0);
            $table->decimal('duree_realisee', 8, 2)->default(0);
            $table->decimal('salaire_journalier', 12, 2)->default(0);
            $table->decimal('montant_du', 12, 2)->default(0);
            $table->decimal('penalite_montant', 12, 2)->default(0);
            $table->string('statut')->default('absent');
            $table->text('observation')->nullable();
            $table->integer('id_user')->default(0);
            $table->timestamps();

            $table->unique(['id_biometrie_import', 'id_emploi_permanent', 'date_presence'], 'uniq_presence_permanent_import_emploi_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presence_permanents');

        Schema::table('biometrie_imports', function (Blueprint $table) {
            if (Schema::hasColumn('biometrie_imports', 'type_import')) {
                $table->dropColumn('type_import');
            }
        });
    }
};
