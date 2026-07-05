<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weekly_league_settlements', function (Blueprint $table): void {
            $table->id();
            $table->date('week_start')->unique();
            $table->foreignId('settled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('settled_at')->nullable();
            $table->timestamps();
        });

        Schema::create('weekly_league_rewards', function (Blueprint $table): void {
            $table->id();
            $table->date('week_start');
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('rank');
            $table->unsignedInteger('score');
            $table->unsignedInteger('reward');
            $table->timestamp('awarded_at');
            $table->timestamps();
            $table->unique(['week_start', 'user_id']);
            $table->unique(['week_start', 'rank']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weekly_league_rewards');
        Schema::dropIfExists('weekly_league_settlements');
    }
};
