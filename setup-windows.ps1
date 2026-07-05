$ErrorActionPreference = "Stop"

$Target = Join-Path $PSScriptRoot "lucky-arcade-app"
if (Test-Path $Target) {
    throw "Thu muc $Target da ton tai. Hay doi ten hoac xoa thu muc cu."
}

if (-not (Get-Command composer -ErrorAction SilentlyContinue)) {
    throw "Khong tim thay Composer. Cai Composer truoc khi chay script."
}

composer create-project laravel/laravel:^13.0 $Target
Copy-Item -Path (Join-Path $PSScriptRoot "overlay\*") -Destination $Target -Recurse -Force
Copy-Item -Path (Join-Path $PSScriptRoot "LICENSE") -Destination (Join-Path $Target "LICENSE") -Force
Copy-Item -Path (Join-Path $PSScriptRoot "docs") -Destination (Join-Path $Target "docs") -Recurse -Force

Set-Location $Target
Copy-Item .env.example .env -Force

$envText = Get-Content .env -Raw
$envText = $envText -replace 'APP_NAME=Laravel', 'APP_NAME="Lucky Arcade"'
$envText = $envText -replace 'DB_CONNECTION=sqlite', 'DB_CONNECTION=sqlite'
$envText = $envText -replace 'SESSION_DRIVER=database', 'SESSION_DRIVER=database'
Set-Content .env $envText -NoNewline

if (-not (Test-Path "database\database.sqlite")) {
    New-Item "database\database.sqlite" -ItemType File | Out-Null
}

php artisan key:generate
php artisan migrate --seed
php artisan arcade:metrics --days=14
php artisan test
php artisan wallets:reconcile
php artisan arcade:backup --keep=10
php artisan arcade:doctor

Write-Host ""
Write-Host "Cai dat thanh cong." -ForegroundColor Green
Write-Host "Chay: cd lucky-arcade-app; php artisan serve"
Write-Host "Admin: admin@example.com / ChangeMe123!"
Write-Host "Demo:  demo@example.com / Demo123!"
