#!/bin/sh
# Healthcheck sesuai peran container (image yang sama dipakai app, queue, scheduler).
# Ringan: tidak menjalankan `php artisan` (boot Laravel tiap 30 detik = beban CPU).

case "${CONTAINER_ROLE:-app}" in
  app)
    # Lewat nginx -> PHP-FPM -> Laravel -> database (bukan sekadar file statis)
    curl -fsS --max-time 8 -o /dev/null http://127.0.0.1/api/health || exit 1
    ;;
  queue)
    pgrep -f "queue:work" >/dev/null 2>&1 || exit 1
    ;;
  scheduler)
    # Heartbeat ditulis tiap menit oleh tugas terjadwal 'health-heartbeat'
    BEAT=/var/www/html/storage/framework/scheduler-heartbeat
    [ -f "$BEAT" ] || exit 1
    AGE=$(( $(date +%s) - $(stat -c %Y "$BEAT") ))
    [ "$AGE" -lt 180 ] || exit 1
    ;;
esac
exit 0
