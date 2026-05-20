<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('etats_paie')) {
            Schema::create('etats_paie', function (Blueprint $table) {
                $table->id();
                $table->string('reference')->unique();
                $table->date('periode_debut')->index();
                $table->date('periode_fin')->index();
                $table->integer('id_annee_academique')->nullable()->index();
                $table->integer('id_entite')->nullable()->index();
                $table->dateTime('date_generation')->index();
                $table->integer('nombre_employes')->default(0);
                $table->decimal('total_gains', 14, 2)->default(0);
                $table->decimal('total_retenues', 14, 2)->default(0);
                $table->decimal('total_penalites', 14, 2)->default(0);
                $table->decimal('total_sanctions', 14, 2)->default(0);
                $table->decimal('total_acomptes', 14, 2)->default(0);
                $table->decimal('total_net_a_payer', 14, 2)->default(0);
                $table->string('statut')->default('genere')->index();
                $table->text('observations')->nullable();
                $table->integer('id_user')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('lignes_etat_paie')) {
            Schema::create('lignes_etat_paie', function (Blueprint $table) {
                $table->id();
                $table->foreignId('id_etat_paie')->constrained('etats_paie')->cascadeOnDelete();
                $table->foreignId('id_bulletin_paie')->nullable()->constrained('bulletins_paie')->nullOnDelete();
                $table->integer('id_personnel')->index();
                $table->string('nom_personnel');
                $table->decimal('salaire_base', 14, 2)->default(0);
                $table->decimal('total_gains', 14, 2)->default(0);
                $table->decimal('total_retenues', 14, 2)->default(0);
                $table->decimal('penalite_biometrie', 14, 2)->default(0);
                $table->decimal('total_sanctions', 14, 2)->default(0);
                $table->decimal('total_acomptes', 14, 2)->default(0);
                $table->decimal('net_a_payer', 14, 2)->default(0);
                $table->json('detail_gains')->nullable();
                $table->json('detail_retenues')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('lignes_etat_paie');
        Schema::dropIfExists('etats_paie');
    }
};
