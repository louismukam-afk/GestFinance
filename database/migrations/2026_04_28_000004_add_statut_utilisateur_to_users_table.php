<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'statut_utilisateur')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'is_super_admin')) {
                    $table->string('statut_utilisateur', 20)->default('non_actif')->after('is_super_admin');
                } else {
                    $table->string('statut_utilisateur', 20)->default('non_actif')->after('password');
                }
            });
        }

        DB::table('users')
            ->whereNull('statut_utilisateur')
            ->orWhere('statut_utilisateur', '')
            ->update(['statut_utilisateur' => 'actif']);

        DB::table('users')
            ->where('id', 1)
            ->update(['statut_utilisateur' => 'actif']);
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'statut_utilisateur')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('statut_utilisateur');
            });
        }
    }
};
