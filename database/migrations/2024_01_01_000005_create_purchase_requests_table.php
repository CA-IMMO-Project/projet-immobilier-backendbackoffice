<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->restrictOnDelete();
            $table->string('client_nom', 100)->nullable();
            $table->string('client_prenom', 100)->nullable();
            $table->string('client_telephone', 30)->nullable();
            $table->string('client_email', 255)->nullable();

            $table->foreignId('terrain_id')->constrained('terrains')->restrictOnDelete();
            $table->string('terrain_titre', 255)->nullable();
            $table->string('terrain_localisation', 255)->nullable();
            $table->decimal('terrain_prix', 18, 0)->nullable();

            $table->text('description'); // NOT NULL : obligatoire

            $table->decimal('prix_propose', 18, 0)->nullable();
            $table->boolean('paiement_comptant')->default(false);
            $table->boolean('facilite_paiement')->default(false);
            $table->integer('duree_paiement')->nullable();
            $table->decimal('apport', 18, 0)->nullable();
            $table->text('infos_supplementaires')->nullable();

            $table->string('status', 50)->default('nouvelle');
            $table->timestamps();

            $table->index(['terrain_id', 'status']);
        });

        DB::statement("ALTER TABLE purchase_requests ADD CONSTRAINT chk_purchase_requests_status
            CHECK (status IN ('nouvelle','en_etude','acceptee','rejetee','annulee'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_requests');
    }
};
