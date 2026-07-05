<?php

namespace App\Console\Commands;

use App\Services\ProductionReadinessService;
use Illuminate\Console\Command;

class ArcadeDoctor extends Command
{
    protected $signature = 'arcade:doctor {--json : Output machine-readable JSON} {--strict : Treat warnings as failures}';

    protected $description = 'Check the production readiness of the Lucky Arcade runtime';

    public function handle(ProductionReadinessService $readiness): int
    {
        $checks = $readiness->checks();

        if ($this->option('json')) {
            $this->line((string) json_encode([
                'ok' => ! $readiness->hasErrors($checks) && (! $this->option('strict') || ! $readiness->hasWarnings($checks)),
                'checks' => $checks,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        } else {
            $this->table(
                ['Check', 'Status', 'Details'],
                collect($checks)->map(fn (array $check): array => [
                    $check['label'], strtoupper($check['status']), $check['message'],
                ])->all(),
            );
        }

        if ($readiness->hasErrors($checks)) {
            return self::FAILURE;
        }

        if ($this->option('strict') && $readiness->hasWarnings($checks)) {
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
