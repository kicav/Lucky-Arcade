<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_entries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('game_id')->constrained()->restrictOnDelete();
            $table->foreignId('fairness_seed_id')->constrained()->restrictOnDelete();
            $table->unsignedBigInteger('stake');
            $table->unsignedBigInteger('payout')->default(0);
            $table->bigInteger('net');
            $table->json('bet');
            $table->json('result');
            $table->string('client_seed', 64);
            $table->unsignedBigInteger('nonce');
            $table->char('server_seed_hash', 64);
            $table->uuid('request_id');
            $table->string('status', 20)->default('settled');
            $table->timestamps();
            $table->unique(['user_id', 'request_id']);
            $table->index(['game_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_entries');
    }
};
