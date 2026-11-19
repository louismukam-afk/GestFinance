<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('rubriques_paie')->where('code', 'ccf')->update([
            'valeur_defaut' => 1,
            'mode_calcul' => 'pourcentage',
            'base_calcul' => 'brut',
            'updated_at' => now(),
        ]);
        DB::table('rubriques_paie')->whereIn('code', ['tdl', 'rav'])->update([
            'mode_calcul' => 'bareme',
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('rubriques_paie')->where('code', 'ccf')->update(['valeur_defaut' => 0, 'base_calcul' => 'taxable']);
    }
};
