<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('nom', 100);
            $table->string('prenom', 100);
            $table->string('numero', 30)->nullable();
            $table->string('mail', 255)->nullable()->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('profession', 150)->nullable();
            $table->string('nationalite', 100)->nullable();
            $table->string('pays_residence', 100)->nullable();
            $table->boolean('acheteur')->default(false);
            $table->boolean('proprietaire')->default(false);
            $table->date('date_naissance')->nullable();
            $table->boolean('compte_bancaire')->default(false);
            $table->text('piece_identite')->nullable();
            $table->string('password', 255)->nullable();
            $table->rememberToken();

            $table->integer('nombre_terrain_propose')->default(0);
            $table->integer('nombre_terrain_publie')->default(0);
            $table->integer('nombre_terrain_vendu')->default(0);
            $table->integer('nombre_demande_recherche')->default(0);
            $table->integer('nombre_demande_achat')->default(0);
            $table->integer('nombre_visite_planifiee')->default(0);
            $table->integer('nombre_visite_effectuee')->default(0);
            $table->integer('nombre_reservation')->default(0);
            $table->integer('nombre_transaction')->default(0);

            $table->string('status', 50)->default('nouveau');
            $table->softDeletes();
            $table->timestamps();
        });

        // Contrainte CHECK sur les valeurs autorisées (section 7 du dictionnaire)
        DB::statement("ALTER TABLE clients ADD CONSTRAINT chk_clients_status
            CHECK (status IN ('nouveau','actif','inactif','liste_noire'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
