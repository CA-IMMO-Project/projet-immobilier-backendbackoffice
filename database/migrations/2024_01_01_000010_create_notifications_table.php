<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('user_name', 201)->nullable();
            $table->string('type', 100)->nullable();
            $table->string('title', 255);
            $table->text('message')->nullable();
            $table->string('priority', 30)->default('normale');
            $table->string('entity_type', 50)->nullable();
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->string('entity_label', 255)->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['user_id', 'read_at']);
        });

        DB::statement("ALTER TABLE notifications ADD CONSTRAINT chk_notifications_priority
            CHECK (priority IN ('basse','normale','haute','urgente'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
