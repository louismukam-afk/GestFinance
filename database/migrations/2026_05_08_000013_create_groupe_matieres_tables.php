<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('groupe_matieres', function (Blueprint $table) {
            $table->id();
            $table->integer('id_specialite')->index();
            $table->integer('id_matiere_parent')->index();
            $table->string('libelle_groupe');
            $table->text('description')->nullable();
            $table->integer('id_user')->default(0);
            $table->timestamps();
        });

        Schema::create('groupe_matiere_lignes', function (Blueprint $table) {
            $table->id();
            $table->integer('id_groupe_matiere')->index();
            $table->integer('id_programme_specialite')->index();
            $table->integer('id_user')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('groupe_matiere_lignes');
        Schema::dropIfExists('groupe_matieres');
    }
};
