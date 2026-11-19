<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('lignes_etat_paie')) {
            return;
        }

        if (!Schema::hasColumn('lignes_etat_paie', 'salaire_taxable')) {
            Schema::table('lignes_etat_paie', function (Blueprint $table) {
                $table->decimal('salaire_taxable', 14, 2)->default(0)->after('salaire_base');
            });
        }

        if (!Schema::hasColumn('lignes_etat_paie', 'salaire_cotisable')) {
            Schema::table('lignes_etat_paie', function (Blueprint $table) {
                $table->decimal('salaire_cotisable', 14, 2)->default(0)->after('salaire_taxable');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('lignes_etat_paie')) {
            return;
        }

        if (Schema::hasColumn('lignes_etat_paie', 'salaire_cotisable')) {
            Schema::table('lignes_etat_paie', function (Blueprint $table) {
                $table->dropColumn('salaire_cotisable');
            });
        }

        if (Schema::hasColumn('lignes_etat_paie', 'salaire_taxable')) {
            Schema::table('lignes_etat_paie', function (Blueprint $table) {
                $table->dropColumn('salaire_taxable');
            });
        }
    }
};
