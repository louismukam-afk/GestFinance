<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('personnels', 'nom_biometrie')) {
            Schema::table('personnels', function (Blueprint $table) {
                $table->string('nom_biometrie')->nullable()->after('nom')->index();
            });
        }

        DB::table('personnels')
            ->where(function ($query) {
                $query->whereNull('nom_biometrie')->orWhere('nom_biometrie', '');
            })
            ->update(['nom_biometrie' => DB::raw('nom')]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('personnels', 'nom_biometrie')) {
            Schema::table('personnels', fn(Blueprint $table) => $table->dropColumn('nom_biometrie'));
        }
    }
};
