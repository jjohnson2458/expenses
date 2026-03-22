#!/bin/bash
# MyExpenses Deployment Script
# Deploys to production via git pull
set -e
REMOTE_HOST="50.16.139.240"
REMOTE_PATH="/var/www/html/expenses"
echo "Deploying MyExpenses to production..."
ssh admin@$REMOTE_HOST "cd $REMOTE_PATH && git pull origin main && php database/migrate.php"
echo "Deployment complete!"
# Send notification
php C:/xampp/htdocs/claude_messenger/notify.php -s "MyExpenses Deployed" -b "<p>MyExpenses has been deployed to production successfully.</p>" -p claude_expenses
