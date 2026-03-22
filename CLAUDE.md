# MyExpenses - Project Instructions

## Overview
Smart, tailored expense reporting application for VisionQuest Services LLC.

## Tech Stack
- PHP 8.1+ MVC framework (custom, no framework)
- MySQL database: `expenses`
- Bootstrap 5 + jQuery
- Session-based authentication

## Directory Structure
- app/Controllers/ - MVC controllers
- app/Models/ - Database models extending base Model
- app/Helpers/ - Database, Router, functions
- config/ - App and database configuration
- database/migrations/ - SQL migration files
- database/seeds/ - Database seeders
- lang/en/, lang/es/ - Translation files
- public/ - Web root (index.php entry point)
- resources/views/ - PHP view templates
- routes/web.php - All route definitions
- storage/ - Logs, cache, sessions
- scripts/ - Deployment and utility scripts

## Key Patterns
- Views use output buffering: ob_start(), build content, $content = ob_get_clean(), include layout
- All controllers extend App\Controllers\Controller (provides view(), redirect(), csrf, auth helpers)
- All models extend App\Models\Model (provides find, all, paginate, create, update, delete, where, search)
- CSRF protection on all POST routes via verifyCsrf()
- Flash messages via $_SESSION['flash']
- Translation function: __t('key')

## Database
- Host: 127.0.0.1 (local), configured in .env
- Run migrations: php database/migrate.php
- Run seeds: php database/seeds/seed.php
- Test database: expenses_test

## Local Development
- URL: http://expenses.local
- Apache vhost required pointing to public/

## Production
- Domain: https://expenses.visionquest2020.net
- Server: 50.16.139.240
- Path: /var/www/html/expenses
- Deploy: scripts/deploy.sh (git pull)

## Special Features
- Voice input for expenses (Web Speech API)
- Receipt OCR (planned - Claude API integration)
- QuickBooks IIF export
- Google Calendar iCal export
- Recurring monthly expenses with auto-processing
- Multiple labeled expense reports
- Credits and debits in ledger
- Drag-and-drop category reordering
- Bilingual: English and Spanish
