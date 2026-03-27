# VQ Money - Project Instructions

## Overview
Smart, tailored expense tracking and tax-ready reporting application for VisionQuest Services LLC.
Product domain: vqmoney.com

## Tech Stack
- Laravel 12 (PHP 8.2+)
- MySQL database: `expenses`
- Bootstrap 5 + jQuery
- Blade templates
- Laravel Auth (session-based)

## Directory Structure
- app/Http/Controllers/ - Laravel controllers
- app/Models/ - Eloquent models
- app/Helpers/ - Custom helper functions
- app/Http/Middleware/ - Custom middleware (SetLocale)
- config/ - Laravel configuration
- database/migrations/ - Laravel migration files
- database/seeders/ - Laravel seeders
- lang/en/, lang/es/ - Translation files (101 keys each)
- public/ - Web root (index.php entry point)
- resources/views/ - Blade templates
- routes/web.php - All route definitions
- storage/ - Logs, cache, sessions, framework
- scripts/ - Deployment and utility scripts
- docs/ - Planning documents (git-ignored)

## Key Patterns
- Blade templates with @extends('layouts.app') and @section('content')
- All controllers in App\Http\Controllers namespace
- Eloquent models with relationships, scopes, and static query methods
- CSRF protection automatic via Laravel middleware
- Flash messages via session('flash') with ['type' => '...', 'message' => '...']
- Translation: __('messages.key') or __t('key') helper
- Auth: Laravel Auth facade, middleware('auth') on routes

## Database
- Host: 127.0.0.1 (local), configured in .env
- Run migrations: php artisan migrate
- Run seeds: php artisan db:seed
- Test database: expenses_test

## Local Development
- URL: http://expenses.local
- Apache vhost pointing to public/

## Production
- Domain: https://vqmoney.com
- Server: 50.16.139.240
- Path: /var/www/html/vqmoney
- Deploy: scripts/deploy.sh (git pull + artisan migrate + cache)

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
- Color theme system (5 themes)
