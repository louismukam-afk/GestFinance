<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('heures_realisees_enseignants', function (Blueprint $table) {
            $table->string('type_requete')->nullable();
            $table->decimal('duree_avant_requete', 8, 2)->nullable();
            $table->decimal('montant_avant_requete', 12, 2)->nullable();
            $table->decimal('sanction_avant_requete', 12, 2)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('heures_realisees_enseignants', fn(Blueprint $table) => $table->dropColumn([
            'type_requete', 'duree_avant_requete', 'montant_avant_requete', 'sanction_avant_requete',
        ]));
    }
};
