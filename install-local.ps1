$ErrorActionPreference = 'Stop'

if (-not (Get-Command php -ErrorAction SilentlyContinue)) {
    throw 'PHP 8.1+ no está instalado o no está disponible en PATH.'
}

if (-not (Get-Command composer -ErrorAction SilentlyContinue)) {
    throw 'Composer no está instalado o no está disponible en PATH.'
}

if (-not (Test-Path '.env')) {
    Copy-Item '.env.example' '.env'
}

if (-not (Test-Path 'database/database.sqlite')) {
    New-Item -ItemType File -Path 'database/database.sqlite' | Out-Null
}

composer install --no-interaction

$appKey = Select-String -Path '.env' -Pattern '^APP_KEY=(.+)$'
if (-not $appKey) {
    php artisan key:generate
}

php artisan migrate --force

if (Get-Command npm -ErrorAction SilentlyContinue) {
    npm ci
    npm run build
}

Write-Host ''
Write-Host 'Instalación local terminada.'
Write-Host 'Ejecute: php artisan serve --host=127.0.0.1'
Write-Host 'Luego abra: http://127.0.0.1:8000'
Write-Host 'El primer registro será el administrador inicial.'
