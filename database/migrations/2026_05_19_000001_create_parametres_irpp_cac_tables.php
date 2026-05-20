<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('baremes_irpp')) {
            Schema::create('baremes_irpp', function (Blueprint $table) {
                $table->id();
                $table->decimal('montant_min', 12, 2)->default(0);
                $table->decimal('montant_max', 12, 2)->nullable();
                $table->decimal('taux', 8, 4)->default(0);
                $table->date('date_debut')->index();
                $table->date('date_fin')->nullable()->index();
                $table->boolean('actif')->default(true)->index();
                $table->integer('ordre')->default(0);
                $table->integer('id_user')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('parametres_cac')) {
            Schema::create('parametres_cac', function (Blueprint $table) {
                $table->id();
                $table->decimal('taux', 8, 4)->default(0);
                $table->date('date_debut')->index();
                $table->date('date_fin')->nullable()->index();
                $table->boolean('actif')->default(true)->index();
                $table->integer('id_user')->default(0);
                $table->timestamps();
            });
        }

        $now = now();
        $dateDebut = '2026-01-01';
        $baremes = [
            [0, 62000, 10, 1],
            [62000, 100000, 15, 2],
            [100000, 150000, 25, 3],
            [150000, 500000, 35, 4],
            [500000, null, 38.5, 5],
        ];

        foreach ($baremes as [$min, $max, $taux, $ordre]) {
            DB::table('baremes_irpp')->updateOrInsert(
                [
                    'montant_min' => $min,
                    'montant_max' => $max,
                    'date_debut' => $dateDebut,
                ],
                [
                    'taux' => $taux,
                    'date_fin' => null,
                    'actif' => true,
                    'ordre' => $ordre,
                    'id_user' => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        DB::table('parametres_cac')->updateOrInsert(
            ['date_debut' => $dateDebut],
            [
                'taux' => 10,
                'date_fin' => null,
                'actif' => true,
                'id_user' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('parametres_cac');
        Schema::dropIfExists('baremes_irpp');
    }
};
