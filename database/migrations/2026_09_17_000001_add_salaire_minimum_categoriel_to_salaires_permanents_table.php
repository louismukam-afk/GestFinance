<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('salaires_permanents', 'salaire_minimum_categoriel')) {
            return;
        }

        Schema::table('salaires_permanents', function (Blueprint $table) {
            $table->decimal('salaire_minimum_categoriel', 12, 2)->nullable()->after('montant_mensuel');
        });
    }

    public function down(): void
    {
        if (!Schema::hasColumn('salaires_permanents', 'salaire_minimum_categoriel')) {
            return;
        }

        Schema::table('salaires_permanents', function (Blueprint $table) {
            $table->dropColumn('salaire_minimum_categoriel');
        });
    }
};
