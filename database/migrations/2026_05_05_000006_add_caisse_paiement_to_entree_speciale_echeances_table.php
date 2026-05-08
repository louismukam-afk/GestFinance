<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('entree_speciale_echeances', function (Blueprint $table) {
            if (!Schema::hasColumn('entree_speciale_echeances', 'id_caisse_paiement')) {
                $table->integer('id_caisse_paiement')->default(0);
            }
        });
    }

    public function down(): void
    {
        Schema::table('entree_speciale_echeances', function (Blueprint $table) {
            if (Schema::hasColumn('entree_speciale_echeances', 'id_caisse_paiement')) {
                $table->dropColumn('id_caisse_paiement');
            }
        });
    }
};
