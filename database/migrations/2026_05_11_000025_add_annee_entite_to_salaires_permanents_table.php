<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salaires_permanents', function (Blueprint $table) {
            if (!Schema::hasColumn('salaires_permanents', 'id_annee_academique')) {
                $table->integer('id_annee_academique')->nullable()->index()->after('id_personnel');
            }

            if (!Schema::hasColumn('salaires_permanents', 'id_entite')) {
                $table->integer('id_entite')->nullable()->index()->after('id_annee_academique');
            }
        });
    }

    public function down(): void
    {
        Schema::table('salaires_permanents', function (Blueprint $table) {
            if (Schema::hasColumn('salaires_permanents', 'id_entite')) {
                $table->dropColumn('id_entite');
            }

            if (Schema::hasColumn('salaires_permanents', 'id_annee_academique')) {
                $table->dropColumn('id_annee_academique');
            }
        });
    }
};
