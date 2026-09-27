<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('terrains', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 50)->unique();
            $table->string('titre', 255);
            $table->text('description')->nullable();
            $table->decimal('superficie', 18, 2)->nullable();
            $table->decimal('prix_m2', 18, 0)->nullable();
            $table->decimal('prix_terrain', 18, 0)->nullable();
            $table->string('zone', 150)->nullable();
            $table->string('localisation', 255)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->text('atouts')->nullable();

            $table->foreignId('proprietaire_id')->constrained('clients')->restrictOnDelete();
            $table->string('proprietaire_nom', 100)->nullable();
            $table->string('proprietaire_prenom', 100)->nullable();
            $table->string('proprietaire_telephone', 30)->nullable();
            $table->string('proprietaire_email', 255)->nullable();
            $table->string('proprietaire_pays_residence', 100)->nullable();
            $table->string('proprietaire_nationalite', 100)->nullable();
            $table->string('proprietaire_profession', 150)->nullable();
            $table->text('proprietaire_piece_identite')->nullable();

            $table->string('title_status', 50)->nullable();
            $table->string('status', 50)->default('brouillon');
            $table->boolean('verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('verified_by_name', 201)->nullable();
            $table->timestamp('published_at')->nullable();

            $table->text('accessibilite')->nullable();
            $table->string('relief', 100)->nullable();
            $table->boolean('paiement_comptant')->default(false);
            $table->boolean('facilite_paiement')->default(false);
            $table->integer('duree_facilite_max')->nullable();
            $table->decimal('acompte', 18, 0)->nullable();
            $table->decimal('prix_achat_client', 18, 0)->nullable();

            $table->integer('nombre_visite_planifiee')->default(0);
            $table->integer('nombre_visite_effectuee')->default(0);
            $table->integer('nombre_demande_achat')->default(0);
            $table->integer('nombre_reservation')->default(0);
            $table->integer('nombre_transaction')->default(0);
            $table->integer('nombre_client_interesse')->default(0);

            $table->jsonb('media')->nullable();
            $table->jsonb('documents')->nullable();

            $table->softDeletes();
            $table->timestamps();

            // Index recommandés (section 6)
            $table->index('proprietaire_id');
            $table->index(['status', 'verified']);
            $table->index('zone');
        });

        DB::statement("ALTER TABLE terrains ADD CONSTRAINT chk_terrains_status
            CHECK (status IN ('brouillon','en_verification','publie','reserve','vendu','retire'))");

        // Index partiel : accélère les listes actives en excluant les terrains supprimés
        DB::statement('CREATE INDEX idx_terrains_deleted_at ON terrains (deleted_at) WHERE deleted_at IS NULL');
    }

    public function down(): void
    {
        Schema::dropIfExists('terrains');
    }
};
