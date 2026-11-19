<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('baremes_retenues_fixes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->index();
            $table->string('libelle');
            $table->decimal('montant_min', 14, 2)->default(0);
            $table->decimal('montant_max', 14, 2)->nullable();
            $table->decimal('montant', 14, 2)->default(0);
            $table->date('date_debut')->index();
            $table->date('date_fin')->nullable();
            $table->boolean('actif')->default(true);
            $table->integer('ordre')->default(0);
            $table->integer('id_user')->default(0);
            $table->timestamps();
        });

        Schema::create('parametres_charges_patronales', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->index();
            $table->string('libelle');
            $table->enum('nature', ['sociale', 'fiscale']);
            $table->enum('base_calcul', ['brut', 'cotisable_plafonne', 'cotisable_non_plafonne']);
            $table->decimal('taux', 8, 4);
            $table->decimal('plafond', 14, 2)->nullable();
            $table->date('date_debut')->index();
            $table->date('date_fin')->nullable();
            $table->boolean('actif')->default(true);
            $table->integer('ordre')->default(0);
            $table->integer('id_user')->default(0);
            $table->timestamps();
        });

        Schema::create('charges_patronales_bulletin', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_bulletin')->constrained('bulletins_paie')->cascadeOnDelete();
            $table->foreignId('id_parametre')->nullable()->constrained('parametres_charges_patronales')->nullOnDelete();
            $table->string('code', 50);
            $table->string('libelle');
            $table->string('nature', 20);
            $table->string('base_calcul', 40);
            $table->decimal('base', 14, 2)->default(0);
            $table->decimal('taux', 8, 4)->default(0);
            $table->decimal('montant', 14, 2)->default(0);
            $table->integer('ordre')->default(0);
            $table->timestamps();
            $table->unique(['id_bulletin', 'code']);
        });

        Schema::table('bulletins_paie', function (Blueprint $table) {
            $table->decimal('base_apres_penalite', 14, 2)->default(0)->after('penalite_biometrie');
            $table->decimal('total_charges_patronales', 14, 2)->default(0)->after('total_sanctions');
        });

        $this->addEtatColumns();
        $this->seedParameters();
    }

    private function addEtatColumns(): void
    {
        Schema::table('etats_paie', function (Blueprint $table) {
            $table->decimal('total_charges_patronales', 14, 2)->default(0)->after('total_acomptes');
        });
        Schema::table('lignes_etat_paie', function (Blueprint $table) {
            $table->decimal('total_charges_patronales', 14, 2)->default(0)->after('total_acomptes');
            $table->json('detail_charges_patronales')->nullable()->after('detail_retenues');
        });
    }

    private function seedParameters(): void
    {
        $now = now();
        $tdl = [[0, 62000, 0], [62000, 75000, 250], [75000, 100000, 500], [100000, 125000, 750], [125000, 150000, 1000], [150000, 200000, 1250], [200000, 250000, 1500], [250000, 300000, 2000], [300000, 500000, 2250], [500000, null, 2500]];
        $rav = [[0, 50000, 0], [50000, 100000, 750], [100000, 200000, 1950], [200000, 300000, 3250], [300000, 400000, 4550], [400000, 500000, 5850], [500000, 600000, 7150], [600000, 700000, 8450], [700000, 800000, 9750], [800000, 900000, 11050], [900000, 1000000, 12350], [1000000, null, 13000]];
        foreach (['tdl' => $tdl, 'rav' => $rav] as $code => $rows) {
            foreach ($rows as $ordre => [$min, $max, $montant]) {
                DB::table('baremes_retenues_fixes')->insert(['code' => $code, 'libelle' => strtoupper($code), 'montant_min' => $min, 'montant_max' => $max, 'montant' => $montant, 'date_debut' => '2000-01-01', 'actif' => true, 'ordre' => $ordre + 1, 'id_user' => 0, 'created_at' => $now, 'updated_at' => $now]);
            }
        }

        $charges = [
            ['allocations_familiales', 'Allocations familiales', 'sociale', 'cotisable_plafonne', 7, 750000],
            ['pension_employeur', 'Pension vieillesse employeur', 'sociale', 'cotisable_plafonne', 4.2, 750000],
            ['accident_travail', 'Accident du travail', 'sociale', 'cotisable_non_plafonne', 1.75, null],
            ['credit_foncier_employeur', 'Credit foncier employeur', 'fiscale', 'brut', 1.5, null],
            ['fne', 'Fonds national de l emploi', 'fiscale', 'brut', 1, null],
        ];
        foreach ($charges as $ordre => [$code, $libelle, $nature, $base, $taux, $plafond]) {
            DB::table('parametres_charges_patronales')->insert(compact('code', 'libelle', 'nature', 'taux', 'plafond') + ['base_calcul' => $base, 'date_debut' => '2000-01-01', 'actif' => true, 'ordre' => $ordre + 1, 'id_user' => 0, 'created_at' => $now, 'updated_at' => $now]);
        }

        DB::table('rubriques_paie')->where('code', 'ccf')->update(['valeur_defaut' => 1, 'mode_calcul' => 'pourcentage', 'base_calcul' => 'brut', 'updated_at' => $now]);
        DB::table('rubriques_paie')->whereIn('code', ['tdl', 'rav'])->update(['mode_calcul' => 'bareme', 'updated_at' => $now]);

        DB::table('baremes_irpp')->update(['actif' => false]);
        foreach ([[0, 2000000, 10], [2000000, 3000000, 15], [3000000, 5000000, 25], [5000000, null, 35]] as $ordre => [$min, $max, $taux]) {
            DB::table('baremes_irpp')->insert(['montant_min' => $min, 'montant_max' => $max, 'taux' => $taux, 'date_debut' => '2000-01-01', 'date_fin' => null, 'actif' => true, 'ordre' => $ordre + 1, 'id_user' => 0, 'created_at' => $now, 'updated_at' => $now]);
        }
    }

    public function down(): void
    {
        Schema::table('lignes_etat_paie', fn(Blueprint $table) => $table->dropColumn(['total_charges_patronales', 'detail_charges_patronales']));
        Schema::table('etats_paie', fn(Blueprint $table) => $table->dropColumn('total_charges_patronales'));
        Schema::table('bulletins_paie', fn(Blueprint $table) => $table->dropColumn(['base_apres_penalite', 'total_charges_patronales']));
        Schema::dropIfExists('charges_patronales_bulletin');
        Schema::dropIfExists('parametres_charges_patronales');
        Schema::dropIfExists('baremes_retenues_fixes');
    }
};
