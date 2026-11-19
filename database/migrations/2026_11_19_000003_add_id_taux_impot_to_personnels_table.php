<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personnels', function (Blueprint $table) {
            $table->foreignId('id_taux_impot')->nullable()->constrained('taux_impots')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('personnels', fn(Blueprint $table) => $table->dropConstrainedForeignId('id_taux_impot'));
    }
};
