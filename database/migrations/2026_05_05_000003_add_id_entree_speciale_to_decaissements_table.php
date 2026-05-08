<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('decaissements', function (Blueprint $table) {
            if (!Schema::hasColumn('decaissements', 'id_entree_speciale')) {
                $table->integer('id_entree_speciale')->default(0);
            }
        });
    }

    public function down(): void
    {
        Schema::table('decaissements', function (Blueprint $table) {
            if (Schema::hasColumn('decaissements', 'id_entree_speciale')) {
                $table->dropColumn('id_entree_speciale');
            }
        });
    }
};
