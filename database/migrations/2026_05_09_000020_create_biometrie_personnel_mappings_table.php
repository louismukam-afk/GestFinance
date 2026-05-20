<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('biometrie_personnel_mappings', function (Blueprint $table) {
            $table->id();
            $table->integer('id_personnel')->index();
            $table->string('nom_biometrie')->nullable()->index();
            $table->string('numero_biometrie')->nullable()->index();
            $table->integer('id_user')->default(0);
            $table->timestamps();

            $table->unique(['id_personnel', 'nom_biometrie', 'numero_biometrie'], 'uniq_bio_personnel_mapping');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('biometrie_personnel_mappings');
    }
};
