<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transfert_caisses', function (Blueprint $table) {
            if (!Schema::hasColumn('transfert_caisses', 'id_banque_depart')) {
                $table->integer('id_banque_depart')->default(0)->after('id_caisse_depart');
            }

            if (!Schema::hasColumn('transfert_caisses', 'id_banque_arrivee')) {
                $table->integer('id_banque_arrivee')->default(0)->after('id_banque_depart');
            }
        });
    }

    public function down(): void
    {
        Schema::table('transfert_caisses', function (Blueprint $table) {
            if (Schema::hasColumn('transfert_caisses', 'id_banque_arrivee')) {
                $table->dropColumn('id_banque_arrivee');
            }

            if (Schema::hasColumn('transfert_caisses', 'id_banque_depart')) {
                $table->dropColumn('id_banque_depart');
            }
        });
    }
};
