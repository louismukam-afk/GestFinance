<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('virements_vacataires', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_biometrie_import')->index();
            $table->unsignedBigInteger('id_entite')->index();
            $table->unsignedBigInteger('id_banque')->index();
            $table->string('entite_nom');
            $table->string('banque_nom');
            $table->date('date_debut');
            $table->date('date_fin');
            $table->date('date_ordre');
            $table->string('modele', 10);
            $table->string('agence');
            $table->string('compte_debit');
            $table->text('motif');
            $table->string('lieu');
            $table->string('signataire');
            $table->string('fonction_signataire');
            $table->unsignedBigInteger('id_user')->nullable();
            $table->timestamp('valide_at');
            $table->timestamps();
        });
        Schema::create('lignes_virement_vacataires', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_virement')->constrained('virements_vacataires')->restrictOnDelete();
            $table->unsignedBigInteger('id_biometrie_import');
            $table->unsignedBigInteger('id_entite');
            $table->unsignedBigInteger('id_personnel');
            $table->string('nom');
            $table->string('compte')->nullable();
            $table->string('banque_personnel')->nullable();
            $table->boolean('sans_compte_confirme')->default(false);
            $table->decimal('brut_fiscal', 15, 2);
            $table->decimal('impot', 15, 2);
            $table->decimal('net_avant_frais', 15, 2);
            $table->decimal('frais_transfert', 15, 2);
            $table->decimal('net_a_payer', 15, 2);
            $table->json('details');
            $table->timestamps();
            $table->unique(['id_biometrie_import', 'id_entite', 'id_personnel'], 'unique_virement_import_entite_personnel');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lignes_virement_vacataires');
        Schema::dropIfExists('virements_vacataires');
    }
};
