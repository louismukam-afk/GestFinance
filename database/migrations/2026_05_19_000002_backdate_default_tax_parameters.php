<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('baremes_irpp')
            ->where('id_user', 0)
            ->whereDate('date_debut', '2026-01-01')
            ->update(['date_debut' => '2000-01-01']);

        DB::table('parametres_cac')
            ->where('id_user', 0)
            ->whereDate('date_debut', '2026-01-01')
            ->update(['date_debut' => '2000-01-01']);
    }

    public function down(): void
    {
        DB::table('baremes_irpp')
            ->where('id_user', 0)
            ->whereDate('date_debut', '2000-01-01')
            ->update(['date_debut' => '2026-01-01']);

        DB::table('parametres_cac')
            ->where('id_user', 0)
            ->whereDate('date_debut', '2000-01-01')
            ->update(['date_debut' => '2026-01-01']);
    }
};
