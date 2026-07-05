<?php

namespace Database\Seeders;

use App\Models\Game;
use App\Models\User;
use App\Services\FairnessSeedService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Game::query()->updateOrCreate(
            ['code' => 'dice'],
            [
                'name' => 'Dice',
                'description' => 'Choose over or under and test a verifiable random roll.',
                'enabled' => true,
                'min_bet' => 10,
                'max_bet' => 1000,
                'config' => ['house_edge' => 0.01],
            ],
        );

        Game::query()->updateOrCreate(
            ['code' => 'roulette'],
            [
                'name' => 'European Roulette',
                'description' => 'Single-zero roulette with standard virtual-credit payouts.',
                'enabled' => true,
                'min_bet' => 10,
                'max_bet' => 1000,
                'config' => ['variant' => 'european'],
            ],
        );

        $this->seedUser('Administrator', 'admin@example.com', 'ChangeMe123!', true, 100000);
        $this->seedUser('Demo Player', 'demo@example.com', 'Demo123!', false, 10000);
    }

    private function seedUser(
        string $name,
        string $email,
        string $password,
        bool $isAdmin,
        int $balance,
    ): void {
        $user = User::query()->firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
                'is_admin' => $isAdmin,
            ],
        );

        $user->wallet()->firstOrCreate([], ['balance' => $balance]);

        if (! $user->fairnessSeeds()->where('active', true)->exists()) {
            app(FairnessSeedService::class)->create($user);
        }
    }
}
