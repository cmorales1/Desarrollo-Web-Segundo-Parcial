@echo off
setlocal
set PROJECT_NAME=proyecto-multitenant
set DB_HOST=127.0.0.1
set DB_PORT=5432
set DB_NAME=central_db
set DB_USER=postgres
set DB_PASS=postgres

echo.
echo === Instalador (PostgreSQL) ===
powershell -ExecutionPolicy Bypass -File ".\setup.ps1" ^
  -ProjectName "%PROJECT_NAME%" ^
  -DBHost "%DB_HOST%" -DBPort %DB_PORT% ^
  -DBName "%DB_NAME%" -DBUser "%DB_USER%" -DBPass "%DB_PASS%"

if %ERRORLEVEL% NEQ 0 (
  echo Hubo un error durante la instalacion. Revisa los mensajes anteriores.
  pause
  exit /b %ERRORLEVEL%
)

echo.
echo Instalacion completa. Entra a %PROJECT_NAME% y ejecuta: php artisan serve
pause
