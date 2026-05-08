<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('programme_specialites', function (Blueprint $table) {
            $table->id();
            $table->integer('id_specialite')->index();
            $table->integer('id_matiere')->index();
            $table->string('code_matiere_specialite')->nullable();
            $table->double('coefficient')->default(0);
            $table->double('coefficient_maximum')->default(0);
            $table->string('type_matiere', 50)->default('professionnelle');
            $table->string('semestre', 50)->nullable();
            $table->integer('id_user')->default(0);
            $table->timestamps();

            $table->unique(['id_specialite', 'id_matiere'], 'programme_specialite_matiere_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('programme_specialites');
    }
};
