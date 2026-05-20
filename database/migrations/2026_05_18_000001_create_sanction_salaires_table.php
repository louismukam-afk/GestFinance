<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sanction_salaires', function (Blueprint $table) {
            $table->id();
            $table->integer('id_personnel')->index();
            $table->date('date_sanction')->index();
            $table->decimal('montant', 12, 2)->default(0);
            $table->string('motif');
            $table->text('description')->nullable();
            $table->char('mois_application', 7)->nullable()->index();
            $table->date('periode_debut_application')->nullable()->index();
            $table->date('periode_fin_application')->nullable()->index();
            $table->string('statut')->default('active')->index();
            $table->unsignedBigInteger('id_bulletin_paie')->nullable()->index();
            $table->integer('id_user')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sanction_salaires');
    }
};
