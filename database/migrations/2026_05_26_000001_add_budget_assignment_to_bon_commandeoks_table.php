<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bon_commandeoks', function (Blueprint $table) {
            if (!Schema::hasColumn('bon_commandeoks', 'id_budget')) {
                $table->integer('id_budget')->nullable()->index()->after('id_entite');
            }
            if (!Schema::hasColumn('bon_commandeoks', 'id_ligne_budgetaire_sortie')) {
                $table->integer('id_ligne_budgetaire_sortie')->nullable()->index()->after('id_budget');
            }
            if (!Schema::hasColumn('bon_commandeoks', 'id_elements_ligne_budgetaire_sortie')) {
                $table->integer('id_elements_ligne_budgetaire_sortie')->nullable()->index()->after('id_ligne_budgetaire_sortie');
            }
            if (!Schema::hasColumn('bon_commandeoks', 'id_donnee_budgetaire_sortie')) {
                $table->integer('id_donnee_budgetaire_sortie')->nullable()->index()->after('id_elements_ligne_budgetaire_sortie');
            }
            if (!Schema::hasColumn('bon_commandeoks', 'id_donnee_ligne_budgetaire_sortie')) {
                $table->integer('id_donnee_ligne_budgetaire_sortie')->nullable()->index()->after('id_donnee_budgetaire_sortie');
            }
            if (!Schema::hasColumn('bon_commandeoks', 'id_annee_academique')) {
                $table->integer('id_annee_academique')->nullable()->index()->after('id_donnee_ligne_budgetaire_sortie');
            }
            if (!Schema::hasColumn('bon_commandeoks', 'id_entree_speciale')) {
                $table->integer('id_entree_speciale')->nullable()->index()->after('id_annee_academique');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bon_commandeoks', function (Blueprint $table) {
            foreach ([
                'id_entree_speciale',
                'id_annee_academique',
                'id_donnee_ligne_budgetaire_sortie',
                'id_donnee_budgetaire_sortie',
                'id_elements_ligne_budgetaire_sortie',
                'id_ligne_budgetaire_sortie',
                'id_budget',
            ] as $column) {
                if (Schema::hasColumn('bon_commandeoks', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
