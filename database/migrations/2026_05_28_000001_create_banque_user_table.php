<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('banque_user')) {
            Schema::create('banque_user', function (Blueprint $table) {
                $table->id();
                $table->foreignId('id_banque')->constrained('banques')->cascadeOnDelete();
                $table->foreignId('id_user')->constrained('users')->cascadeOnDelete();
                $table->boolean('peut_encaisser')->default(false);
                $table->boolean('peut_decaisser')->default(false);
                $table->boolean('actif')->default(true);
                $table->date('date_debut')->nullable();
                $table->date('date_fin')->nullable();
                $table->text('observation')->nullable();
                $table->timestamps();

                $table->unique(['id_banque', 'id_user']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('banque_user');
    }
};
