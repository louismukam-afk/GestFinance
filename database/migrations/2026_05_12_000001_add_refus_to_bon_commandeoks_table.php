<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bon_commandeoks', function (Blueprint $table) {
            $table->boolean('refus_pdg')->default(false)->after('validation_pdg');
            $table->text('motif_refus_pdg')->nullable()->after('refus_pdg');
            $table->timestamp('date_refus_pdg')->nullable()->after('motif_refus_pdg');

            $table->boolean('refus_daf')->default(false)->after('validation_daf');
            $table->text('motif_refus_daf')->nullable()->after('refus_daf');
            $table->timestamp('date_refus_daf')->nullable()->after('motif_refus_daf');

            $table->boolean('refus_achats')->default(false)->after('validation_achats');
            $table->text('motif_refus_achats')->nullable()->after('refus_achats');
            $table->timestamp('date_refus_achats')->nullable()->after('motif_refus_achats');

            $table->boolean('refus_emetteur')->default(false)->after('validation_emetteur');
            $table->text('motif_refus_emetteur')->nullable()->after('refus_emetteur');
            $table->timestamp('date_refus_emetteur')->nullable()->after('motif_refus_emetteur');
        });
    }

    public function down(): void
    {
        Schema::table('bon_commandeoks', function (Blueprint $table) {
            $table->dropColumn([
                'refus_pdg',
                'motif_refus_pdg',
                'date_refus_pdg',
                'refus_daf',
                'motif_refus_daf',
                'date_refus_daf',
                'refus_achats',
                'motif_refus_achats',
                'date_refus_achats',
                'refus_emetteur',
                'motif_refus_emetteur',
                'date_refus_emetteur',
            ]);
        });
    }
};
