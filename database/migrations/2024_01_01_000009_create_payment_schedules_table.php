<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained('transactions')->restrictOnDelete();
            $table->foreignId('client_id')->constrained('clients')->restrictOnDelete();
            $table->string('client_nom', 100)->nullable();
            $table->string('client_telephone', 30)->nullable();

            $table->foreignId('terrain_id')->constrained('terrains')->restrictOnDelete();
            $table->string('terrain_titre', 255)->nullable();

            $table->integer('numero_echeance');
            $table->date('date_prevue');
            $table->decimal('montant', 18, 0);
            $table->decimal('montant_paye', 18, 0)->default(0);

            $table->string('status', 50)->default('en_attente');
            $table->date('date_paiement')->nullable();
            $table->text('commentaire')->nullable();

            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('recorded_by_name', 201)->nullable();
            $table->timestamps();

            $table->index(['transaction_id', 'status']);
        });

        DB::statement("ALTER TABLE payment_schedules ADD CONSTRAINT chk_payment_schedules_status
            CHECK (status IN ('en_attente','paye','partiellement_paye','en_retard','annule'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_schedules');
    }
};
