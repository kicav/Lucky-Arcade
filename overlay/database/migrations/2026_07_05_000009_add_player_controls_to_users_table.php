<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->unsignedBigInteger('daily_stake_limit')->nullable()->after('is_admin');
            $table->timestamp('self_excluded_until')->nullable()->after('daily_stake_limit');
            $table->timestamp('suspended_at')->nullable()->after('self_excluded_until');
            $table->string('suspension_reason', 255)->nullable()->after('suspended_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn([
                'daily_stake_limit',
                'self_excluded_until',
                'suspended_at',
                'suspension_reason',
            ]);
        });
    }
};
