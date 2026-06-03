<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('entree_speciales', function (Blueprint $table) {
            if (!Schema::hasColumn('entree_speciales', 'id_banque')) {
                $table->integer('id_banque')->default(0)->after('id_caisse');
            }
        });
    }

    public function down(): void
    {
        Schema::table('entree_speciales', function (Blueprint $table) {
            if (Schema::hasColumn('entree_speciales', 'id_banque')) {
                $table->dropColumn('id_banque');
            }
        });
    }
};
