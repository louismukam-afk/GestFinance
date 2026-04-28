<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('retour_caisses') && !Schema::hasColumn('retour_caisses', 'id_decaissement')) {
            Schema::table('retour_caisses', function (Blueprint $table) {
                $table->unsignedBigInteger('id_decaissement')->nullable()->after('id_bon_commande');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('retour_caisses') && Schema::hasColumn('retour_caisses', 'id_decaissement')) {
            Schema::table('retour_caisses', function (Blueprint $table) {
                $table->dropColumn('id_decaissement');
            });
        }
    }
};
