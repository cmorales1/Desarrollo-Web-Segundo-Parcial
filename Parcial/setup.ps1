param(
  [string]$ProjectName = "proyecto-multitenant",
  [string]$DBHost = "127.0.0.1",
  [int]$DBPort = 5432,
  [string]$DBName = "central_db",
  [string]$DBUser = "postgres",
  [string]$DBPass = "postgres"
)

$ErrorActionPreference = "Stop"

function Check-Cmd($name) {
  $cmd = Get-Command $name -ErrorAction SilentlyContinue
  if (-not $cmd) { throw "No se encontró el comando '$name' en PATH." }
}

Write-Host "== Verificando dependencias ==" -ForegroundColor Cyan
Check-Cmd php
Check-Cmd composer
Check-Cmd npm

Write-Host "== Recordatorio: habilita pdo_pgsql y pgsql en php.ini ==" -ForegroundColor Yellow

Write-Host "== Creando proyecto Laravel ==" -ForegroundColor Cyan
composer create-project laravel/laravel:^12.0 $ProjectName --no-interaction

Set-Location $ProjectName

Write-Host "== Instalando Sanctum ==" -ForegroundColor Cyan
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider" --force

# Ajustar .env para PostgreSQL
Write-Host "== Configurando .env para PostgreSQL ==" -ForegroundColor Cyan
if (Test-Path "..\env.template") { Copy-Item "..\env.template" ".env" -Force }
(Get-Content ".\.env") |
  ForEach-Object {
    $_ -replace '^DB_CONNECTION=.*$', "DB_CONNECTION=pgsql" `
       -replace '^DB_HOST=.*$', "DB_HOST=$DBHost" `
       -replace '^DB_PORT=.*$', "DB_PORT=$DBPort" `
       -replace '^DB_DATABASE=.*$', "DB_DATABASE=$DBName" `
       -replace '^DB_USERNAME=.*$', "DB_USERNAME=$DBUser" `
       -replace '^DB_PASSWORD=.*$', "DB_PASSWORD=$DBPass"
  } | Set-Content ".\.env" -Encoding UTF8

php artisan key:generate --force

Write-Host "== Copiando archivos del proyecto (src) ==" -ForegroundColor Cyan
$src = Join-Path $PSScriptRoot "src"
function Copy-Tree($from, $to) {
  if (-not (Test-Path $from)) { return }
  Get-ChildItem -Path $from -Recurse | ForEach-Object {
    $rel = $_.FullName.Substring($from.Length).TrimStart('\','/')
    $dest = Join-Path $to $rel
    if ($_.PSIsContainer) {
      if (-not (Test-Path $dest)) { New-Item -ItemType Directory -Path $dest | Out-Null }
    } else {
      New-Item -ItemType Directory -Force -Path (Split-Path $dest) | Out-Null
      Copy-Item $_.FullName $dest -Force
    }
  }
}
Copy-Tree $src (Get-Location).Path

Write-Host "== Asegurando Sanctum en Kernel API ==" -ForegroundColor Cyan
$kernelPath = "app\Http\Kernel.php"
$kernel = Get-Content $kernelPath -Raw
if ($kernel -notmatch "EnsureFrontendRequestsAreStateful") {
  $kernel = $kernel -replace "(?s)('api'\s*=>\s*\[\s*)", "`$1\Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,"
  Set-Content $kernelPath $kernel -Encoding UTF8
}

Write-Host "== Registrando TenantSeeder ==" -ForegroundColor Cyan
$dsPath = "database\seeders\DatabaseSeeder.php"
$ds = Get-Content $dsPath -Raw
if ($ds -notmatch "TenantSeeder") {
  $ds = $ds -replace "(?s)public function run\(\): void\s*\{\s*", "public function run(): void {`n        \$this->call(\\Database\\Seeders\\TenantSeeder::class);`n"
  Set-Content $dsPath $ds -Encoding UTF8
}

Write-Host "== Migraciones base (usuarios/jobs) ==" -ForegroundColor Cyan
php artisan migrate --force

Write-Host "== Ejecutando seeder de tenants ==" -ForegroundColor Cyan
php artisan db:seed --class=TenantSeeder --force

Write-Host "== Migrando schemas de cada tenant ==" -ForegroundColor Cyan
composer dump-autoload -q
php artisan tenants:migrate

Write-Host "== Instalando Vue/axios ==" -ForegroundColor Cyan
npm install
npm install vue @vitejs/plugin-vue axios

Write-Host ''
Write-Host '===============================' -ForegroundColor Green
Write-Host ' INSTALACIÓN COMPLETA (PostgreSQL)' -ForegroundColor Green
Write-Host (' Proyecto: {0}' -f $ProjectName) -ForegroundColor Green
Write-Host ' 1) Revisa .env (DB y APP_URL)' -ForegroundColor Green
Write-Host ' 2) Crea usuario de prueba: php artisan tinker -> User::create([...])' -ForegroundColor Green
Write-Host ' 3) Ejecuta: php artisan serve' -ForegroundColor Green
Write-Host '===============================' -ForegroundColor Green