@echo off
echo ====================================================
echo  Starting Development Mode (with Vite Hot Reload)
echo ====================================================
echo.

REM Copy .env.docker to .env if .env doesn't exist
if not exist .env (
    echo Copying .env.docker to .env...
    copy .env.docker .env
)

echo Starting Docker containers with Vite...
docker-compose --profile development up -d

echo.
echo Waiting for services to be ready...
timeout /t 15 /nobreak >nul

echo.
echo Running database migrations...
docker-compose exec app php artisan migrate --force

echo.
echo Creating storage link...
docker-compose exec app php artisan storage:link

echo.
echo ====================================================
echo  Development Mode Started!
echo ====================================================
echo.
echo  Web Application: http://localhost:8000
echo  Vite Dev Server: http://localhost:5173
echo  PostgreSQL:      localhost:5432
echo.
echo  Hot Module Replacement (HMR) is ENABLED
echo  Changes to CSS/JS will auto-reload
echo.
echo ====================================================
echo.
echo To view logs:     docker-compose logs -f
echo To stop all:      docker-compose --profile development down
echo To stop Vite:     docker stop bel_sekolah_vite
echo.
pause
