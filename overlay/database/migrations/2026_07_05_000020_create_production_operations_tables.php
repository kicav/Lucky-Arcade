<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_game_metrics', function (Blueprint $table): void {
            $table->id();
            $table->date('metric_date');
            $table->foreignId('game_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('plays')->default(0);
            $table->unsignedBigInteger('wins')->default(0);
            $table->unsignedBigInteger('total_stake')->default(0);
            $table->unsignedBigInteger('total_payout')->default(0);
            $table->bigInteger('system_net')->default(0);
            $table->timestamps();
            $table->unique(['metric_date', 'game_id']);
            $table->index(['metric_date', 'updated_at']);
        });

        Schema::create('operation_runs', function (Blueprint $table): void {
            $table->id();
            $table->string('task', 100)->index();
            $table->string('status', 20)->index();
            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();
            $table->unsignedBigInteger('duration_ms')->nullable();
            $table->json('details')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
            $table->index(['task', 'started_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operation_runs');
        Schema::dropIfExists('daily_game_metrics');
    }
};
