<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('niveaux', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('nom_niveau');
            $table->string('code_niveau');
            $table->integer('id_user')->default(0);
            $table->integer('id_cycle')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('niveaux');
    }
};
