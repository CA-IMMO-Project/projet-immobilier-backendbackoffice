<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
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

            $table->timestamp('date_reservation');
            $table->timestamp('date_fin_reservation')->nullable();
            $table->decimal('prix_reservation', 18, 0)->nullable();
            $table->decimal('acompte', 18, 0)->nullable();

            $table->string('status', 50)->default('active');
            $table->timestamps();

            $table->index(['terrain_id', 'status']);
        });

        DB::statement("ALTER TABLE reservations ADD CONSTRAINT chk_reservations_status
            CHECK (status IN ('active','expiree','annulee','transformee_en_vente'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
