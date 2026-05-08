<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('programme_specialites', function (Blueprint $table) {
            $table->dropUnique('programme_specialite_matiere_unique');

            if (!Schema::hasColumn('programme_specialites', 'id_cycle')) {
                $table->integer('id_cycle')->default(0)->after('id_specialite')->index();
            }
            if (!Schema::hasColumn('programme_specialites', 'id_filiere')) {
                $table->integer('id_filiere')->default(0)->after('id_cycle')->index();
            }
            if (!Schema::hasColumn('programme_specialites', 'id_niveau')) {
                $table->integer('id_niveau')->default(0)->after('id_filiere')->index();
            }
            if (!Schema::hasColumn('programme_specialites', 'id_annee_academique')) {
                $table->integer('id_annee_academique')->default(0)->after('id_niveau')->index();
            }
            if (!Schema::hasColumn('programme_specialites', 'id_entite')) {
                $table->integer('id_entite')->default(0)->after('id_annee_academique')->index();
            }

            $table->unique([
                'id_specialite',
                'id_cycle',
                'id_filiere',
                'id_niveau',
                'id_annee_academique',
                'id_entite',
                'id_matiere',
            ], 'programme_specialite_context_unique');
        });
    }

    public function down(): void
    {
        Schema::table('programme_specialites', function (Blueprint $table) {
            $table->dropUnique('programme_specialite_context_unique');
            $table->unique(['id_specialite', 'id_matiere'], 'programme_specialite_matiere_unique');

            foreach (['id_cycle', 'id_filiere', 'id_niveau', 'id_annee_academique', 'id_entite'] as $column) {
                if (Schema::hasColumn('programme_specialites', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
