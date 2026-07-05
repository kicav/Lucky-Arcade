<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('live_events', function (Blueprint $table): void {
            $table->id();
            $table->string('audience', 20)->default('user');
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('topic', 80)->index();
            $table->string('event_type', 120)->index();
            $table->json('payload')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['audience', 'id']);
            $table->index(['user_id', 'id']);
        });

        Schema::create('user_presences', function (Blueprint $table): void {
            $table->unsignedBigInteger('user_id')->primary();
            $table->string('current_path', 500)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 1000)->nullable();
            $table->timestamp('last_seen_at')->index();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_presences');
        Schema::dropIfExists('live_events');
    }
};
