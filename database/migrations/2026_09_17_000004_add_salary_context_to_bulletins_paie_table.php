<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bulletins_paie', function (Blueprint $table) {
            $table->foreignId('id_salaire_permanent')
                ->nullable()
                ->after('id_biometrie_import')
                ->constrained('salaires_permanents')
                ->nullOnDelete();
            $table->integer('id_annee_academique')->nullable()->after('id_salaire_permanent')->index();
            $table->integer('id_entite')->nullable()->after('id_annee_academique')->index();
        });

        DB::table('bulletins_paie')->orderBy('id')->chunkById(100, function ($bulletins) {
            foreach ($bulletins as $bulletin) {
                $salaire = DB::table('salaires_permanents')
                    ->where('id_personnel', $bulletin->id_personnel)
                    ->whereDate('date_debut', '<=', $bulletin->periode_fin)
                    ->where(function ($query) use ($bulletin) {
                        $query->whereNull('date_fin')
                            ->orWhereDate('date_fin', '>=', $bulletin->periode_debut);
                    })
                    ->orderByDesc('date_debut')
                    ->first();

                if ($salaire) {
                    DB::table('bulletins_paie')->where('id', $bulletin->id)->update([
                        'id_salaire_permanent' => $salaire->id,
                        'id_annee_academique' => $salaire->id_annee_academique,
                        'id_entite' => $salaire->id_entite,
                    ]);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('bulletins_paie', function (Blueprint $table) {
            $table->dropForeign(['id_salaire_permanent']);
            $table->dropColumn(['id_salaire_permanent', 'id_annee_academique', 'id_entite']);
        });
    }
};
