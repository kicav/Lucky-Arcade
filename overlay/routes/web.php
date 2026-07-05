<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin\AuditLogController as AdminAuditLogController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\AnalyticsController as AdminAnalyticsController;
use App\Http\Controllers\Admin\EntryController as AdminEntryController;
use App\Http\Controllers\Admin\GameController as AdminGameController;
use App\Http\Controllers\Admin\UserActionController as AdminUserActionController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\TwoFactorChallengeController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CoinFlipController;
use App\Http\Controllers\DailyRewardController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DiceController;
use App\Http\Controllers\FairnessController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\HighLowController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\AchievementController;
use App\Http\Controllers\LedgerExportController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReferralController;
use App\Http\Controllers\RouletteController;
use App\Http\Controllers\SlotsController;
use App\Http\Controllers\MissionController;
use App\Http\Controllers\PlayerStatsController;
use App\Http\Controllers\Admin\AnnouncementController as AdminAnnouncementController;
use App\Http\Controllers\PromoCodeController;
use App\Http\Controllers\SupportTicketController;
use App\Http\Controllers\WeeklyLeagueController;
use App\Http\Controllers\SecurityController;
use App\Http\Controllers\Admin\PromoCodeController as AdminPromoCodeController;
use App\Http\Controllers\Admin\SupportTicketController as AdminSupportTicketController;
use App\Http\Controllers\Admin\WeeklyLeagueController as AdminWeeklyLeagueController;
use App\Http\Controllers\Admin\AdminAccessController;
use App\Http\Controllers\Admin\SystemController as AdminSystemController;
use App\Http\Controllers\Admin\SecurityEventController as AdminSecurityEventController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/leaderboard', LeaderboardController::class)->name('leaderboard');

Route::middleware('guest')->group(function (): void {
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->middleware('throttle:5,1')->name('register.store');
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:8,1')->name('login.store');
    Route::get('/two-factor-challenge', [TwoFactorChallengeController::class, 'create'])->name('two-factor.challenge');
    Route::post('/two-factor-challenge', [TwoFactorChallengeController::class, 'store'])->middleware('throttle:8,1')->name('two-factor.challenge.store');
    Route::post('/two-factor-challenge/cancel', [TwoFactorChallengeController::class, 'cancel'])->name('two-factor.challenge.cancel');
});

Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function (): void {
    Route::get('/support', [SupportTicketController::class, 'index'])->name('support.index');
    Route::post('/support', [SupportTicketController::class, 'store'])->middleware('throttle:5,1')->name('support.store');
    Route::get('/support/{ticket}', [SupportTicketController::class, 'show'])->name('support.show');
    Route::post('/support/{ticket}/reply', [SupportTicketController::class, 'reply'])->middleware('throttle:10,1')->name('support.reply');
    Route::post('/support/{ticket}/close', [SupportTicketController::class, 'close'])->name('support.close');

    Route::get('/security', [SecurityController::class, 'show'])->name('security.show');
    Route::post('/security/two-factor/begin', [SecurityController::class, 'begin'])->middleware('throttle:5,1')->name('security.two-factor.begin');
    Route::post('/security/two-factor/confirm', [SecurityController::class, 'confirm'])->middleware('throttle:8,1')->name('security.two-factor.confirm');
    Route::delete('/security/two-factor', [SecurityController::class, 'disable'])->middleware('throttle:5,1')->name('security.two-factor.disable');
    Route::post('/security/recovery-codes', [SecurityController::class, 'regenerateRecoveryCodes'])->middleware('throttle:5,1')->name('security.recovery-codes.regenerate');
});

Route::middleware(['auth', 'active'])->group(function (): void {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/ledger/export', LedgerExportController::class)->middleware('throttle:10,1')->name('ledger.export');
    Route::post('/daily-reward', [DailyRewardController::class, 'store'])->middleware('throttle:3,1')->name('daily-reward.store');

    Route::get('/account', [AccountController::class, 'show'])->name('account.show');
    Route::put('/account/profile', [AccountController::class, 'updateProfile'])->name('account.profile.update');
    Route::put('/account/password', [AccountController::class, 'updatePassword'])->middleware('throttle:5,1')->name('account.password.update');
    Route::put('/account/play-controls', [AccountController::class, 'updatePlayControls'])->name('account.controls.update');

    Route::get('/achievements', AchievementController::class)->name('achievements.index');
    Route::get('/referrals', ReferralController::class)->name('referrals.index');
    Route::get('/missions', [MissionController::class, 'index'])->name('missions.index');
    Route::post('/missions/{mission}/claim', [MissionController::class, 'claim'])->middleware('throttle:10,1')->name('missions.claim');
    Route::get('/stats', PlayerStatsController::class)->name('stats.index');

    Route::get('/promos', [PromoCodeController::class, 'index'])->name('promos.index');
    Route::post('/promos/redeem', [PromoCodeController::class, 'redeem'])->middleware('throttle:5,1')->name('promos.redeem');
    Route::get('/weekly-league', WeeklyLeagueController::class)->name('league.index');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');

    Route::get('/games', [GameController::class, 'index'])->name('games.index');
    Route::get('/games/{game}', [GameController::class, 'show'])->name('games.show');
    Route::post('/games/{game}/dice', [DiceController::class, 'store'])->middleware('throttle:30,1')->name('games.dice.play');
    Route::post('/games/{game}/roulette', [RouletteController::class, 'store'])->middleware('throttle:30,1')->name('games.roulette.play');
    Route::post('/games/{game}/coinflip', [CoinFlipController::class, 'store'])->middleware('throttle:30,1')->name('games.coinflip.play');
    Route::post('/games/{game}/highlow', [HighLowController::class, 'store'])->middleware('throttle:30,1')->name('games.highlow.play');
    Route::post('/games/{game}/slots', [SlotsController::class, 'store'])->middleware('throttle:30,1')->name('games.slots.play');

    Route::get('/fairness', [FairnessController::class, 'show'])->name('fairness.show');
    Route::post('/fairness/rotate', [FairnessController::class, 'rotate'])->middleware('throttle:5,1')->name('fairness.rotate');
    Route::post('/fairness/verify', [FairnessController::class, 'verify'])->middleware('throttle:20,1')->name('fairness.verify');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function (): void {
    Route::get('/', AdminDashboardController::class)->middleware('admin.area:dashboard')->name('dashboard');
    Route::get('/analytics', AdminAnalyticsController::class)->middleware('admin.area:analytics')->name('analytics');

    Route::get('/games', [AdminGameController::class, 'index'])->middleware('admin.area:games')->name('games.index');
    Route::put('/games/{game}', [AdminGameController::class, 'update'])->middleware('admin.area:games')->name('games.update');

    Route::get('/users', [AdminUserController::class, 'index'])->middleware('admin.area:users')->name('users.index');
    Route::get('/users/{user}', [AdminUserActionController::class, 'show'])->middleware('admin.area:users')->name('users.show');
    Route::post('/users/{user}/suspend', [AdminUserActionController::class, 'suspend'])->middleware('admin.area:user_actions')->name('users.suspend');
    Route::post('/users/{user}/unsuspend', [AdminUserActionController::class, 'unsuspend'])->middleware('admin.area:user_actions')->name('users.unsuspend');
    Route::post('/users/{user}/grant', [AdminUserActionController::class, 'grant'])->middleware(['admin.area:user_actions', 'throttle:10,1'])->name('users.grant');

    Route::get('/entries', [AdminEntryController::class, 'index'])->middleware('admin.area:entries')->name('entries.index');
    Route::get('/audit-logs', [AdminAuditLogController::class, 'index'])->middleware('admin.area:audit')->name('audit.index');
    Route::get('/security-events', [AdminSecurityEventController::class, 'index'])->middleware('admin.area:audit')->name('security-events.index');

    Route::get('/announcements', [AdminAnnouncementController::class, 'index'])->middleware('admin.area:announcements')->name('announcements.index');
    Route::post('/announcements', [AdminAnnouncementController::class, 'store'])->middleware('admin.area:announcements')->name('announcements.store');
    Route::put('/announcements/{announcement}', [AdminAnnouncementController::class, 'update'])->middleware('admin.area:announcements')->name('announcements.update');
    Route::delete('/announcements/{announcement}', [AdminAnnouncementController::class, 'destroy'])->middleware('admin.area:announcements')->name('announcements.destroy');

    Route::get('/promo-codes', [AdminPromoCodeController::class, 'index'])->middleware('admin.area:promos')->name('promos.index');
    Route::post('/promo-codes', [AdminPromoCodeController::class, 'store'])->middleware('admin.area:promos')->name('promos.store');
    Route::put('/promo-codes/{promo}', [AdminPromoCodeController::class, 'update'])->middleware('admin.area:promos')->name('promos.update');
    Route::post('/promo-codes/{promo}/toggle', [AdminPromoCodeController::class, 'toggle'])->middleware('admin.area:promos')->name('promos.toggle');

    Route::get('/support', [AdminSupportTicketController::class, 'index'])->middleware('admin.area:support')->name('support.index');
    Route::get('/support/{ticket}', [AdminSupportTicketController::class, 'show'])->middleware('admin.area:support')->name('support.show');
    Route::post('/support/{ticket}/reply', [AdminSupportTicketController::class, 'reply'])->middleware('admin.area:support')->name('support.reply');
    Route::put('/support/{ticket}/status', [AdminSupportTicketController::class, 'status'])->middleware('admin.area:support')->name('support.status');

    Route::get('/weekly-league', [AdminWeeklyLeagueController::class, 'index'])->middleware('admin.area:league')->name('league.index');
    Route::post('/weekly-league/settle', [AdminWeeklyLeagueController::class, 'settle'])->middleware(['admin.area:league', 'throttle:2,1'])->name('league.settle');

    Route::get('/access', [AdminAccessController::class, 'index'])->middleware('admin.area:access')->name('access.index');
    Route::put('/access/{user}', [AdminAccessController::class, 'update'])->middleware('admin.area:access')->name('access.update');

    Route::get('/system', [AdminSystemController::class, 'index'])->middleware('admin.area:system')->name('system.index');
    Route::post('/system/backup', [AdminSystemController::class, 'backup'])->middleware(['admin.area:system', 'throttle:2,1'])->name('system.backup');
});
