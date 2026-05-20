<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('rubriques_paie')) {
        Schema::create('rubriques_paie', function (Blueprint $table) {
            $table->id();
            $table->string('code', 80)->unique();
            $table->string('libelle');
            $table->string('type')->default('gain')->index();
            $table->string('mode_calcul')->default('fixe');
            $table->string('base_calcul')->nullable();
            $table->decimal('valeur_defaut', 12, 4)->default(0);
            $table->decimal('plafond', 12, 2)->nullable();
            $table->boolean('imposable')->default(true);
            $table->boolean('cotisable')->default(true);
            $table->boolean('systeme')->default(false);
            $table->boolean('actif')->default(true);
            $table->integer('ordre')->default(0);
            $table->integer('id_user')->default(0);
            $table->timestamps();
        });
        }

        if (!Schema::hasTable('config_rubriques_personnel')) {
        Schema::create('config_rubriques_personnel', function (Blueprint $table) {
            $table->id();
            $table->integer('id_personnel')->index();
            $table->foreignId('id_rubrique_paie')->constrained('rubriques_paie')->cascadeOnDelete();
            $table->date('date_debut')->index();
            $table->date('date_fin')->nullable()->index();
            $table->decimal('valeur', 12, 4)->default(0);
            $table->decimal('quantite', 12, 4)->default(1);
            $table->boolean('appliquer_ce_mois')->default(true);
            $table->string('statut')->default('actif')->index();
            $table->text('observations')->nullable();
            $table->integer('id_user')->default(0);
            $table->timestamps();
        });
        }

        if (!Schema::hasTable('bulletins_paie')) {
        Schema::create('bulletins_paie', function (Blueprint $table) {
            $table->id();
            $table->integer('id_personnel')->index();
            $table->foreignId('id_biometrie_import')->nullable()->constrained('biometrie_imports')->nullOnDelete();
            $table->date('periode_debut')->index();
            $table->date('periode_fin')->index();
            $table->decimal('salaire_base', 12, 2)->default(0);
            $table->decimal('penalite_biometrie', 12, 2)->default(0);
            $table->decimal('brut_mensuel', 12, 2)->default(0);
            $table->decimal('salaire_taxable', 12, 2)->default(0);
            $table->decimal('salaire_cotisable', 12, 2)->default(0);
            $table->decimal('total_gains', 12, 2)->default(0);
            $table->decimal('total_retenues', 12, 2)->default(0);
            $table->decimal('total_acomptes', 12, 2)->default(0);
            $table->decimal('total_sanctions', 12, 2)->default(0);
            $table->decimal('net_a_payer', 12, 2)->default(0);
            $table->decimal('solde_du', 12, 2)->default(0);
            $table->string('statut')->default('brouillon')->index();
            $table->text('observations')->nullable();
            $table->integer('id_user')->default(0);
            $table->timestamps();

            $table->unique(['id_personnel', 'periode_debut', 'periode_fin'], 'uniq_bulletin_personnel_periode');
        });
        }

        if (!Schema::hasTable('lignes_bulletin_paie')) {
        Schema::create('lignes_bulletin_paie', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_bulletin')->constrained('bulletins_paie')->cascadeOnDelete();
            $table->foreignId('id_rubrique_paie')->nullable()->constrained('rubriques_paie')->nullOnDelete();
            $table->string('code', 80)->nullable();
            $table->string('libelle');
            $table->string('type')->index();
            $table->string('sens')->index();
            $table->string('mode_calcul')->nullable();
            $table->string('base_calcul')->nullable();
            $table->decimal('base', 12, 2)->default(0);
            $table->decimal('taux', 12, 4)->default(0);
            $table->decimal('quantite', 12, 4)->default(1);
            $table->decimal('montant', 12, 2)->default(0);
            $table->boolean('imposable')->default(false);
            $table->boolean('cotisable')->default(false);
            $table->integer('ordre')->default(0);
            $table->text('observations')->nullable();
            $table->timestamps();
        });
        }

        if (!Schema::hasTable('acomptes_salaire')) {
        Schema::create('acomptes_salaire', function (Blueprint $table) {
            $table->id();
            $table->integer('id_personnel')->index();
            $table->date('date_acompte')->index();
            $table->decimal('montant', 12, 2)->default(0);
            $table->char('periode_imputation', 7)->index();
            $table->string('motif')->nullable();
            $table->string('statut')->default('actif')->index();
            $table->unsignedBigInteger('id_bulletin_paie')->nullable()->index();
            $table->integer('id_user')->default(0);
            $table->timestamps();
        });
        }

        $now = now();
        $rubriques = [
            ['prime_anciennete', 'Prime d anciennete', 'gain', 'pourcentage', 'salaire_base', 0, null, true, true, false, 10],
            ['prime_representation', 'Prime de representation', 'gain', 'fixe', null, 0, null, true, false, false, 20],
            ['indemnite_deplacement', 'Indemnite de deplacement', 'gain', 'fixe', null, 0, null, false, false, false, 30],
            ['indemnite_responsabilite', 'Indemnite de responsabilite', 'gain', 'pourcentage', 'salaire_base', 0, null, true, true, false, 40],
            ['indemnite_kilometrique', 'Indemnite kilometrique', 'gain', 'kilometrage', null, 0, null, false, false, false, 50],
            ['prime_risque', 'Prime de risque', 'gain', 'pourcentage', 'salaire_base', 0, null, true, true, false, 60],
            ['prime_panier', 'Prime de panier', 'gain', 'fixe', null, 0, null, false, false, false, 70],
            ['prime_lait', 'Prime de lait', 'gain', 'fixe', null, 0, null, false, false, false, 80],
            ['prime_salissure', 'Prime de salissure', 'gain', 'pourcentage', 'salaire_base', 0, null, true, true, false, 90],
            ['cnps_salarial', 'CNPS salariale', 'retenue', 'pourcentage', 'cotisable', 4.2, 750000, false, false, true, 200],
            ['irpp', 'IRPP', 'retenue', 'bareme', 'taxable', 0, null, false, false, true, 210],
            ['cac', 'CAC', 'retenue', 'pourcentage', 'irpp', 10, null, false, false, true, 220],
            ['rav', 'RAV', 'retenue', 'fixe', null, 0, null, false, false, true, 230],
            ['ccf', 'CCF', 'retenue', 'pourcentage', 'taxable', 0, null, false, false, true, 240],
            ['tdl', 'TDL', 'retenue', 'pourcentage', 'taxable', 0, null, false, false, true, 250],
        ];

        foreach ($rubriques as $row) {
            DB::table('rubriques_paie')->updateOrInsert(
                ['code' => $row[0]],
                [
                    'libelle' => $row[1],
                    'type' => $row[2],
                    'mode_calcul' => $row[3],
                    'base_calcul' => $row[4],
                    'valeur_defaut' => $row[5],
                    'plafond' => $row[6],
                    'imposable' => $row[7],
                    'cotisable' => $row[8],
                    'systeme' => $row[9],
                    'actif' => true,
                    'ordre' => $row[10],
                    'id_user' => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('acomptes_salaire');
        Schema::dropIfExists('lignes_bulletin_paie');
        Schema::dropIfExists('bulletins_paie');
        Schema::dropIfExists('config_rubriques_personnel');
        Schema::dropIfExists('rubriques_paie');
    }
};
