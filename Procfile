# Procfile for Laravel Application
# Defines processes to run in production

# Web server (required)
web: php artisan serve --host=0.0.0.0 --port=${PORT:-8080}

# Queue worker (optional - uncomment if using queues)
# worker: php artisan queue:work --verbose --tries=3 --timeout=90

# Scheduler (optional - uncomment if using scheduled tasks)
# scheduler: while true; do php artisan schedule:run --verbose --no-interaction; sleep 60; done
