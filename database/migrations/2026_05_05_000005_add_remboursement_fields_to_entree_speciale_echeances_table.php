<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('entree_speciale_echeances', function (Blueprint $table) {
            if (!Schema::hasColumn('entree_speciale_echeances', 'montant_paye')) {
                $table->double('montant_paye')->default(0);
            }

            if (!Schema::hasColumn('entree_speciale_echeances', 'id_annee_academique_paiement')) {
                $table->integer('id_annee_academique_paiement')->default(0);
            }

            if (!Schema::hasColumn('entree_speciale_echeances', 'id_user_paiement')) {
                $table->integer('id_user_paiement')->default(0);
            }
        });
    }

    public function down(): void
    {
        Schema::table('entree_speciale_echeances', function (Blueprint $table) {
            if (Schema::hasColumn('entree_speciale_echeances', 'montant_paye')) {
                $table->dropColumn('montant_paye');
            }

            if (Schema::hasColumn('entree_speciale_echeances', 'id_annee_academique_paiement')) {
                $table->dropColumn('id_annee_academique_paiement');
            }

            if (Schema::hasColumn('entree_speciale_echeances', 'id_user_paiement')) {
                $table->dropColumn('id_user_paiement');
            }
        });
    }
};
