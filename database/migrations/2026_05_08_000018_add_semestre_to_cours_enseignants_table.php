<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cours_enseignants', function (Blueprint $table) {
            if (!Schema::hasColumn('cours_enseignants', 'semestre')) {
                $table->unsignedTinyInteger('semestre')->nullable()->after('statut');
            }
        });
    }

    public function down(): void
    {
        Schema::table('cours_enseignants', function (Blueprint $table) {
            if (Schema::hasColumn('cours_enseignants', 'semestre')) {
                $table->dropColumn('semestre');
            }
        });
    }
};
