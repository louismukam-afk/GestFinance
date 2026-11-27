<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('heures_realisees_enseignants', function (Blueprint $table) {
            $table->unique(
                ['id_biometrie_import', 'id_seance_cours', 'date_seance', 'heure_debut_prevue'],
                'uniq_bio_seance_segment'
            );
            $table->dropUnique('uniq_bio_seance_date');
        });
    }

    public function down(): void
    {
        Schema::table('heures_realisees_enseignants', function (Blueprint $table) {
            $table->unique(['id_biometrie_import', 'id_seance_cours', 'date_seance'], 'uniq_bio_seance_date');
            $table->dropUnique('uniq_bio_seance_segment');
        });
    }
};
