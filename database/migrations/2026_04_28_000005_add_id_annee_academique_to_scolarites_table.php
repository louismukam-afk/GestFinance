<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scolarites', function (Blueprint $table) {
            if (!Schema::hasColumn('scolarites', 'id_annee_academique')) {
                $table->unsignedBigInteger('id_annee_academique')->nullable()->after('id_user')->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('scolarites', function (Blueprint $table) {
            if (Schema::hasColumn('scolarites', 'id_annee_academique')) {
                $table->dropIndex(['id_annee_academique']);
                $table->dropColumn('id_annee_academique');
            }
        });
    }
};
