<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('taux_impots', function (Blueprint $table) {
            $table->id();
            $table->string('nom')->unique();
            $table->decimal('taux', 5, 2);
            $table->boolean('par_defaut')->default(false);
            $table->timestamps();
        });
        DB::table('taux_impots')->insert([
            'nom' => 'Impot vacataires 5,5 %', 'taux' => 5.50, 'par_defaut' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('taux_impots');
    }
};
