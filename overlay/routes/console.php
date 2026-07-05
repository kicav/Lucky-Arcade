<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('arcade:metrics --days=2')
    ->everyFifteenMinutes()
    ->withoutOverlapping(10);

Schedule::command('wallets:reconcile')
    ->dailyAt('02:00')
    ->withoutOverlapping(30);

Schedule::command('arcade:backup --keep=14')
    ->dailyAt('02:20')
    ->withoutOverlapping(60);

Schedule::command('arcade:prune')
    ->dailyAt('03:00')
    ->withoutOverlapping(30);

Schedule::command('queue:prune-failed --hours=168')
    ->dailyAt('03:30')
    ->withoutOverlapping(30);

Schedule::command('arcade:prune-live')
    ->hourly()
    ->withoutOverlapping(10);
