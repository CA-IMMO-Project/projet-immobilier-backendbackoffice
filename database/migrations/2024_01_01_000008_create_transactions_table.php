<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 50)->unique();

            $table->foreignId('client_id')->constrained('clients')->restrictOnDelete();
            $table->string('client_nom', 100)->nullable();
            $table->string('client_prenom', 100)->nullable();
            $table->string('client_telephone', 30)->nullable();
            $table->string('client_email', 255)->nullable();

            $table->foreignId('proprietaire_id')->constrained('clients')->restrictOnDelete();
            $table->string('proprietaire_nom', 100)->nullable();
            $table->string('proprietaire_prenom', 100)->nullable();
            $table->string('proprietaire_telephone', 30)->nullable();

            $table->foreignId('terrain_id')->constrained('terrains')->restrictOnDelete();
            $table->string('terrain_titre', 255)->nullable();
            $table->string('terrain_localisation', 255)->nullable();
            $table->decimal('superficie', 18, 2)->nullable();

            $table->foreignId('reservation_id')->nullable()->constrained('reservations')->nullOnDelete();

            $table->decimal('prix_final', 18, 0)->nullable();
            $table->decimal('acompte', 18, 0)->nullable();
            $table->boolean('paiement_comptant')->default(false);
            $table->boolean('facilite_paiement')->default(false);
            $table->integer('duree_paiement')->nullable();
            $table->decimal('montant_paye', 18, 0)->default(0);
            $table->decimal('montant_restant', 18, 0)->nullable();
            $table->date('prochaine_echeance')->nullable();

            $table->string('status', 50)->default('en_cours');
            $table->timestamp('date_finalisation')->nullable();
            $table->timestamps();

            $table->index('terrain_id');
        });

        DB::statement("ALTER TABLE transactions ADD CONSTRAINT chk_transactions_status
            CHECK (status IN ('en_cours','en_attente_paiement','finalisee','annulee','litige'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
