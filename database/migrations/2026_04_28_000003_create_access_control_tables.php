<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'is_super_admin')) {
                $table->boolean('is_super_admin')->default(false)->after('password');
            }

            if (!Schema::hasColumn('users', 'statut_utilisateur')) {
                $table->string('statut_utilisateur', 20)->default('non_actif')->after('is_super_admin');
            }
        });

        if (Schema::hasColumn('users', 'statut_utilisateur')) {
            DB::table('users')->update(['statut_utilisateur' => 'actif']);
        }

        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('route_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('route_name')->unique();
            $table->string('method', 20)->nullable();
            $table->string('uri')->nullable();
            $table->string('label')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('role_route_permission', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->foreignId('route_permission_id')->constrained('route_permissions')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['role_id', 'route_permission_id'], 'role_permission_unique');
        });

        Schema::create('role_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['role_id', 'user_id'], 'role_user_unique');
        });

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('user_name')->nullable();
            $table->string('method', 20);
            $table->string('route_name')->nullable();
            $table->string('uri');
            $table->string('ip_address', 60)->nullable();
            $table->string('user_agent')->nullable();
            $table->json('payload')->nullable();
            $table->unsignedSmallInteger('status_code')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('role_user');
        Schema::dropIfExists('role_route_permission');
        Schema::dropIfExists('route_permissions');
        Schema::dropIfExists('roles');

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'is_super_admin')) {
                $table->dropColumn('is_super_admin');
            }

            if (Schema::hasColumn('users', 'statut_utilisateur')) {
                $table->dropColumn('statut_utilisateur');
            }
        });
    }
};
