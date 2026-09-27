<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('nom', 100);
            $table->string('prenom', 100);
            $table->string('email', 255)->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('telephone', 30)->nullable();
            $table->string('password', 255);
            $table->rememberToken(); // remember_token VARCHAR(100)
            $table->string('role', 50)->default('admin');
            $table->boolean('actif')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->softDeletes(); // deleted_at
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
