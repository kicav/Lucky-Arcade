<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin\AuditLogController as AdminAuditLogController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\EntryController as AdminEntryController;
use App\Http\Controllers\Admin\GameController as AdminGameController;
use App\Http\Controllers\Admin\UserActionController as AdminUserActionController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CoinFlipController;
use App\Http\Controllers\DailyRewardController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DiceController;
use App\Http\Controllers\FairnessController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\LedgerExportController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\RouletteController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/leaderboard', LeaderboardController::class)->name('leaderboard');

Route::middleware('guest')->group(function (): void {
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->middleware('throttle:5,1')->name('register.store');
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:8,1')->name('login.store');
});

Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware(['auth', 'active'])->group(function (): void {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/ledger/export', LedgerExportController::class)->middleware('throttle:10,1')->name('ledger.export');
    Route::post('/daily-reward', [DailyRewardController::class, 'store'])->middleware('throttle:3,1')->name('daily-reward.store');

    Route::get('/account', [AccountController::class, 'show'])->name('account.show');
    Route::put('/account/profile', [AccountController::class, 'updateProfile'])->name('account.profile.update');
    Route::put('/account/password', [AccountController::class, 'updatePassword'])->middleware('throttle:5,1')->name('account.password.update');
    Route::put('/account/play-controls', [AccountController::class, 'updatePlayControls'])->name('account.controls.update');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');

    Route::get('/games', [GameController::class, 'index'])->name('games.index');
    Route::get('/games/{game}', [GameController::class, 'show'])->name('games.show');
    Route::post('/games/{game}/dice', [DiceController::class, 'store'])->middleware('throttle:30,1')->name('games.dice.play');
    Route::post('/games/{game}/roulette', [RouletteController::class, 'store'])->middleware('throttle:30,1')->name('games.roulette.play');
    Route::post('/games/{game}/coinflip', [CoinFlipController::class, 'store'])->middleware('throttle:30,1')->name('games.coinflip.play');

    Route::get('/fairness', [FairnessController::class, 'show'])->name('fairness.show');
    Route::post('/fairness/rotate', [FairnessController::class, 'rotate'])->middleware('throttle:5,1')->name('fairness.rotate');
    Route::post('/fairness/verify', [FairnessController::class, 'verify'])->middleware('throttle:20,1')->name('fairness.verify');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function (): void {
    Route::get('/', AdminDashboardController::class)->name('dashboard');
    Route::get('/games', [AdminGameController::class, 'index'])->name('games.index');
    Route::put('/games/{game}', [AdminGameController::class, 'update'])->name('games.update');
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/users/{user}', [AdminUserActionController::class, 'show'])->name('users.show');
    Route::post('/users/{user}/suspend', [AdminUserActionController::class, 'suspend'])->name('users.suspend');
    Route::post('/users/{user}/unsuspend', [AdminUserActionController::class, 'unsuspend'])->name('users.unsuspend');
    Route::post('/users/{user}/grant', [AdminUserActionController::class, 'grant'])->middleware('throttle:10,1')->name('users.grant');
    Route::get('/entries', [AdminEntryController::class, 'index'])->name('entries.index');
    Route::get('/audit-logs', [AdminAuditLogController::class, 'index'])->name('audit.index');
});
