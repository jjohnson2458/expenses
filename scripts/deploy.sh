#!/bin/bash
# VQ Money Deployment Script
# Deploys to production via git pull + Laravel optimization
set -e

REMOTE_HOST="50.16.139.240"
REMOTE_PATH="/var/www/html/vqmoney"

echo "Deploying VQ Money to production..."

ssh admin@$REMOTE_HOST "cd $REMOTE_PATH && \
    git pull origin main && \
    composer install --no-dev --optimize-autoloader && \
    php artisan migrate --force && \
    php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache"

echo "Deployment complete!"

# Send notification
php C:/xampp/htdocs/claude_messenger/notify.php -s "VQ Money Deployed" -b "<p>VQ Money has been deployed to production successfully.</p>" -p claude_expenses
