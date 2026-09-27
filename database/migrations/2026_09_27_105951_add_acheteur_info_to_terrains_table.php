<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('terrains',function(Blueprint $table){
            $table->dropForeign(['proprietaire_id']);
        });

        Schema::table('terrains',function(Blueprint $table){
            $table->unsignedBigInteger('proprietaire_id')->nullable()->change();
        });

        Schema::table('terrains',function(Blueprint $table){
            $table->foreign('proprietaire_id')->references('id')->on('clients')->restrictOnDelete();
        });

        Schema::table('terrains', function (Blueprint $table) {
            $table->foreignId('acheteur_id')->nullable()->after('prix_achat_client')
            ->constraint('clients')->restrictOnDelete();
            $table->string('acheteur_nom',100)->nullable()->after('acheteur_id');
            $table->string('acheteur_prenom',100)->nullable()->after('acheteur_nom');
            $table->string('acheteur_telephone',30)->nullable()->after('acheteur_prenom');
            $table->string('acheteur_email',255)->nullable()->after('acheteur_telephone');
            $table->string('acheteur_pays_residence',100)->nullable()->after('acheteur_email');
            $table->string('acheteur_nationalite',100)->nullable()->after('acheteur_pays_residence');
            $table->string('acheteur_profession',100)->nullable()->after('acheteur_nationalite');
            $table->string('acheteur_piece_identite',100)->nullable()->after('acheteur_profession');

            $table->index('acheteur_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('terrains', function (Blueprint $table) {
            $table->dropConstrainedForeignId('acheteur_id');
            $table->dropColumn(['acheteur_nom','acheteur_prenom','acheteur_telephone','acheteur_email',
            'acheteur_pays_residence','acheteur_nationalite','acheteur_profession','acheteur_piece_identite']);
        });

        Schema::table('terrains',function(Blueprint $table){
            $table->dropForeign(['proprietaire_id']);
        });

        Schema::table('terrains',function(Blueprint $table){
            $table->unsignedBigInteger('proprietaire_id')->nullable(false)->change();
        });

        Schema::table('terrains',function(Blueprint $table){
            $table->foreign('proprietaire_id')->references('id')->on('clients')->restrictOnDelete();
        });

    }
};
