@echo off
echo ================================================
echo  Stopping Bel Sekolah Application
echo ================================================
echo.

echo Stopping all Docker containers...
docker-compose down

echo.
echo ================================================
echo  All containers stopped successfully!
echo ================================================
echo.
pause
