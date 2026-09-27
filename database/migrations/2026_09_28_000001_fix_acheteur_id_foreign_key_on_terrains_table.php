<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ->constrained('clients') au lieu de constraint('clients): Fluent::__call() a silencieusement absorbé l'appel sans
        // erreur ni contrainte réelle : acheteur_id existe en base mais n'a jamais eu de
        // vraie clé étrangère vers clients. On la crée ici correctement.
        Schema::table('terrains', function (Blueprint $table) {
            $table->foreign('acheteur_id')->references('id')->on('clients')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('terrains', function (Blueprint $table) {
            $table->dropForeign(['acheteur_id']);
        });
    }
};
