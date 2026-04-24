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
        Schema::create('transfert_caisses', function (Blueprint $table) {
            $table->id();
            $table->string('observation')->default(0);
            $table->string('code_transfert');
            $table->integer('type_transfert')->default(0);
            $table->double('sode_caisse')->default(0);
            $table->double('montant_transfert')->default(0);
            $table->integer('id_caisse_arrivee')->default(0);
            $table->integer('id_caisse_depart')->default(0);
            $table->datetime('date_transfert');
            $table->integer('statut_caisse_transfert')->default(0);
            $table->integer('id_user')->default(0);
            $table->integer('id_last_editor')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfert_caisses');
    }
};
