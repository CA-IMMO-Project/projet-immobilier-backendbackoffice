<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visits', function (Blueprint $table) {
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

            $table->foreignId('purchase_request_id')->nullable()->constrained('purchase_requests')->nullOnDelete();

            $table->date('date_visite');
            $table->time('heure_visite');

            $table->foreignId('responsable_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('responsable_nom', 201)->nullable();
            $table->string('responsable_telephone', 30)->nullable();

            $table->string('status', 50)->default('demandee');
            $table->text('commentaires')->nullable();
            $table->timestamps();

            $table->index(['terrain_id', 'date_visite']);
        });

        DB::statement("ALTER TABLE visits ADD CONSTRAINT chk_visits_status
            CHECK (status IN ('demandee','a_confirmer','confirmee','reportee','effectuee','annulee'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};
