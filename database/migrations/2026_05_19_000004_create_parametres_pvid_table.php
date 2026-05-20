<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('parametres_pvid')) {
            Schema::create('parametres_pvid', function (Blueprint $table) {
                $table->id();
                $table->decimal('taux', 8, 4)->default(0);
                $table->decimal('plafond', 12, 2)->nullable();
                $table->date('date_debut')->index();
                $table->date('date_fin')->nullable()->index();
                $table->boolean('actif')->default(true)->index();
                $table->integer('id_user')->default(0);
                $table->timestamps();
            });
        }

        DB::table('parametres_pvid')->updateOrInsert(
            ['date_debut' => '2000-01-01'],
            [
                'taux' => 4.2,
                'plafond' => 750000,
                'date_fin' => null,
                'actif' => true,
                'id_user' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('parametres_pvid');
    }
};
