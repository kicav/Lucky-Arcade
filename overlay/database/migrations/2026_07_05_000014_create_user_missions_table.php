<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_missions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('mission_key', 80);
            $table->string('title');
            $table->string('description', 500);
            $table->date('mission_date');
            $table->unsignedInteger('progress')->default(0);
            $table->unsignedInteger('target');
            $table->unsignedBigInteger('reward');
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('claimed_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'mission_date', 'mission_key']);
            $table->index(['user_id', 'mission_date', 'claimed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_missions');
    }
};
