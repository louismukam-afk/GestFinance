<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transfert_caisses', function (Blueprint $table) {
            if (!Schema::hasColumn('transfert_caisses', 'id_entree_speciale')) {
                $table->integer('id_entree_speciale')->default(0)->after('id_caisse_depart')->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('transfert_caisses', function (Blueprint $table) {
            if (Schema::hasColumn('transfert_caisses', 'id_entree_speciale')) {
                $table->dropColumn('id_entree_speciale');
            }
        });
    }
};
