<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('decaissements', function (Blueprint $table) {
            $table->double('reste')->default(0);
            $table->string('statut_financement')->default('En cours');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('decaissements', function (Blueprint $table) {
            //
        });
    }
};
