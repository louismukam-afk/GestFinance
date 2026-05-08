<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('entree_speciales', function (Blueprint $table) {
            if (!Schema::hasColumn('entree_speciales', 'id_annee_academique_utilisation')) {
                $table->integer('id_annee_academique_utilisation')->default(0);
            }

            if (!Schema::hasColumn('entree_speciales', 'id_annee_academique_remboursement')) {
                $table->integer('id_annee_academique_remboursement')->default(0);
            }
        });

        DB::table('entree_speciales')
            ->where('id_annee_academique_remboursement', 0)
            ->update([
                'id_annee_academique_remboursement' => DB::raw('id_annee_academique'),
                'id_annee_academique_utilisation' => DB::raw('id_annee_academique'),
            ]);
    }

    public function down(): void
    {
        Schema::table('entree_speciales', function (Blueprint $table) {
            if (Schema::hasColumn('entree_speciales', 'id_annee_academique_utilisation')) {
                $table->dropColumn('id_annee_academique_utilisation');
            }

            if (Schema::hasColumn('entree_speciales', 'id_annee_academique_remboursement')) {
                $table->dropColumn('id_annee_academique_remboursement');
            }
        });
    }
};
