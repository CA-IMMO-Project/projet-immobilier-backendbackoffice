<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('search_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->restrictOnDelete();
            $table->string('client_nom', 100)->nullable();
            $table->string('client_prenom', 100)->nullable();
            $table->string('client_telephone', 30)->nullable();
            $table->string('client_email', 255)->nullable();

            $table->text('description'); // NOT NULL : obligatoire

            $table->string('zone_recherche', 255)->nullable();
            $table->decimal('budget_min', 18, 0)->nullable();
            $table->decimal('budget_max', 18, 0)->nullable();
            $table->decimal('superficie_min', 18, 2)->nullable();
            $table->decimal('superficie_max', 18, 2)->nullable();
            $table->string('relief', 100)->nullable();
            $table->string('usage', 150)->nullable();
            $table->boolean('paiement_comptant')->default(false);
            $table->boolean('facilite_paiement')->default(false);
            $table->integer('duree_paiement')->nullable();
            $table->decimal('apport', 18, 0)->nullable();
            $table->text('infos_supplementaires')->nullable();

            $table->string('status', 50)->default('nouvelle');
            $table->timestamps();

            $table->index('client_id');
        });

        DB::statement("ALTER TABLE search_requests ADD CONSTRAINT chk_search_requests_status
            CHECK (status IN ('nouvelle','en_traitement','proposition_envoyee','cloturee','annulee'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('search_requests');
    }
};
