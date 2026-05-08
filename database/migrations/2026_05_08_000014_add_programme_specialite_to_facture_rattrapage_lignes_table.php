<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('facture_rattrapage_lignes', function (Blueprint $table) {
            if (!Schema::hasColumn('facture_rattrapage_lignes', 'id_programme_specialite')) {
                $table->integer('id_programme_specialite')->default(0)->after('id_matiere')->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('facture_rattrapage_lignes', function (Blueprint $table) {
            if (Schema::hasColumn('facture_rattrapage_lignes', 'id_programme_specialite')) {
                $table->dropColumn('id_programme_specialite');
            }
        });
    }
};
