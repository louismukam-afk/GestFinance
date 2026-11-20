<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('heures_realisees_enseignants', fn(Blueprint $t) => $t->boolean('sanction_annulee')->default(false));
        Schema::table('biometrie_imports', function (Blueprint $t) {
            $t->unsignedBigInteger('id_import_parent')->nullable()->index();
            $t->date('mois_requete')->nullable();
            $t->text('motif_requete')->nullable();
        });
        Schema::table('virements_vacataires', function (Blueprint $t) {
            $t->string('nature')->default('initial')->index();
            $t->unsignedBigInteger('id_import_parent')->nullable()->index();
            $t->date('mois_requete')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('heures_realisees_enseignants', fn(Blueprint $t) => $t->dropColumn('sanction_annulee'));
        Schema::table('virements_vacataires', fn(Blueprint $t) => $t->dropColumn(['nature', 'id_import_parent', 'mois_requete']));
        Schema::table('biometrie_imports', fn(Blueprint $t) => $t->dropColumn(['id_import_parent', 'mois_requete', 'motif_requete']));
    }
};
