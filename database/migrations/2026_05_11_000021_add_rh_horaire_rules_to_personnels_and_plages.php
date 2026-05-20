<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personnels', function (Blueprint $table) {
            if (!Schema::hasColumn('personnels', 'mode_horaire')) {
                $table->string('mode_horaire', 30)->default('strict')->after('type_personnel');
            }
            if (!Schema::hasColumn('personnels', 'categorie_horaire')) {
                $table->string('categorie_horaire', 50)->default('standard')->after('mode_horaire');
            }
            if (!Schema::hasColumn('personnels', 'horaire_travail')) {
                $table->string('horaire_travail', 50)->nullable()->after('categorie_horaire');
            }
        });

        Schema::table('plage_horaires', function (Blueprint $table) {
            if (!Schema::hasColumn('plage_horaires', 'type_personnel')) {
                $table->string('type_personnel', 30)->default('tous')->after('type_plage');
            }
            if (!Schema::hasColumn('plage_horaires', 'periode_journee')) {
                $table->string('periode_journee', 30)->default('jour')->after('type_personnel');
            }
            if (!Schema::hasColumn('plage_horaires', 'format_plage')) {
                $table->string('format_plage', 30)->default('mixte')->after('periode_journee');
            }
            if (!Schema::hasColumn('plage_horaires', 'duree_payable')) {
                $table->decimal('duree_payable', 8, 2)->nullable()->after('heure_fin');
            }
        });

        $this->seedDefaultPlages();
    }

    public function down(): void
    {
        Schema::table('plage_horaires', function (Blueprint $table) {
            foreach (['duree_payable', 'format_plage', 'periode_journee', 'type_personnel'] as $column) {
                if (Schema::hasColumn('plage_horaires', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('personnels', function (Blueprint $table) {
            foreach (['horaire_travail', 'categorie_horaire', 'mode_horaire'] as $column) {
                if (Schema::hasColumn('personnels', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    private function seedDefaultPlages(): void
    {
        $plages = [
            ['libelle' => 'Permanent jour', 'heure_debut' => '08:00:00', 'heure_fin' => '16:00:00', 'duree_payable' => 8, 'type_personnel' => 'permanent', 'periode_journee' => 'jour', 'format_plage' => 'bloc_8h', 'ordre' => 10],
            ['libelle' => 'Permanent soir', 'heure_debut' => '15:00:00', 'heure_fin' => '21:00:00', 'duree_payable' => 6, 'type_personnel' => 'permanent', 'periode_journee' => 'soir', 'format_plage' => 'bloc_6h', 'ordre' => 20],
            ['libelle' => 'Vacataire jour complet', 'heure_debut' => '08:00:00', 'heure_fin' => '16:45:00', 'duree_payable' => 8, 'type_personnel' => 'vacataire', 'periode_journee' => 'jour', 'format_plage' => 'bloc_8h', 'ordre' => 30],
            ['libelle' => 'Vacataire matin', 'heure_debut' => '08:00:00', 'heure_fin' => '12:00:00', 'duree_payable' => 4, 'type_personnel' => 'vacataire', 'periode_journee' => 'jour', 'format_plage' => 'bloc_4h', 'ordre' => 31],
            ['libelle' => 'Pause', 'heure_debut' => '12:00:00', 'heure_fin' => '12:45:00', 'duree_payable' => 0, 'type_personnel' => 'tous', 'periode_journee' => 'jour', 'format_plage' => 'pause', 'type_plage' => 'pause', 'ordre' => 32],
            ['libelle' => 'Vacataire apres-midi', 'heure_debut' => '12:45:00', 'heure_fin' => '16:45:00', 'duree_payable' => 4, 'type_personnel' => 'vacataire', 'periode_journee' => 'jour', 'format_plage' => 'bloc_4h', 'ordre' => 33],
            ['libelle' => 'Vacataire soir', 'heure_debut' => '16:00:00', 'heure_fin' => '21:00:00', 'duree_payable' => 5, 'type_personnel' => 'vacataire', 'periode_journee' => 'soir', 'format_plage' => 'bloc_5h', 'ordre' => 40],
        ];

        foreach ($plages as $plage) {
            DB::table('plage_horaires')->updateOrInsert(
                [
                    'libelle' => $plage['libelle'],
                    'heure_debut' => $plage['heure_debut'],
                    'heure_fin' => $plage['heure_fin'],
                ],
                array_merge([
                    'type_plage' => 'cours',
                    'statut' => 'actif',
                    'id_user' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ], $plage)
            );
        }
    }
};
