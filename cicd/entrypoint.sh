#!/bin/bash
set -e
docker-php-entrypoint "$@"
php /var/www/html/yii migrate --interactive 0
echo "--- Настрока задач CRON ---"
/opt/scripts/cron-setup.sh
echo "--- Запуск Apache ---"
exec apache2-foreground