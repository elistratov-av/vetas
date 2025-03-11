#!/bin/bash
# cron-setup.sh
echo "* * * * * root /usr/local/bin/php /var/www/html/yii televet/cancel-unpaid" > /etc/cron.d/every-minute-tasks
echo "0 0 * * * root /usr/local/bin/php /var/www/html/yii log-clean/external-api" > /etc/cron.d/daily-tasks
chmod 0644 /etc/cron.d/every-minute-tasks
chmod 0644 /etc/cron.d/daily-tasks
cron -f &