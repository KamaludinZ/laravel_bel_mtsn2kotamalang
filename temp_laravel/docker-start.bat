@echo off
echo ================================================
echo  Starting Bel Sekolah Application with Docker
echo ================================================
echo.

REM Copy .env.docker to .env if .env doesn't exist
if not exist .env (
    echo Copying .env.docker to .env...
    copy .env.docker .env
)

echo Starting Docker containers...
docker-compose up -d

echo.
echo Waiting for PostgreSQL to be ready...
timeout /t 10 /nobreak >nul

echo.
echo Running database migrations...
docker-compose exec app php artisan migrate --force

echo.
echo Creating storage link...
docker-compose exec app php artisan storage:link

echo.
echo ================================================
echo  Application started successfully!
echo ================================================
echo.
echo  Web Application: http://localhost:8000
echo  PostgreSQL:      localhost:5432
echo.
echo  Login credentials:
echo  Email:    admin@mtsn2kotamalang.sch.id
echo  Password: password
echo.
echo ================================================
echo.
echo To view logs: docker-compose logs -f
echo To stop:      docker-compose down
echo.
pause
