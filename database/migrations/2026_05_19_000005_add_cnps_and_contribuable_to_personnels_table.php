<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personnels', function (Blueprint $table) {
            if (!Schema::hasColumn('personnels', 'numero_cnps')) {
                $table->string('numero_cnps')->nullable()->after('telephone_whatsapp');
            }

            if (!Schema::hasColumn('personnels', 'numero_contribuable')) {
                $table->string('numero_contribuable')->nullable()->after('numero_cnps');
            }
        });
    }

    public function down(): void
    {
        Schema::table('personnels', function (Blueprint $table) {
            if (Schema::hasColumn('personnels', 'numero_contribuable')) {
                $table->dropColumn('numero_contribuable');
            }

            if (Schema::hasColumn('personnels', 'numero_cnps')) {
                $table->dropColumn('numero_cnps');
            }
        });
    }
};
