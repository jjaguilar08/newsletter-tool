# Newsletter Tool

A portfolio rebuild of an internal newsletter tool: staff manage a subscriber list and send newsletters with editable content on a static layout.

This repo is the Laravel API backend. The frontend is a separate React/TS SPA — [newsletter-tool-web](https://github.com/jjaguilar08/newsletter-tool-web) — authenticating against this API via [Laravel Sanctum](https://laravel.com/docs/sanctum) SPA (cookie-based) auth.

## Stack

- Laravel 13, PHP 8.3+
- Pest for testing
- Sanctum for SPA auth
- SQLite for local dev

## Status

Backend feature-complete: subscriber CRUD/CSV import/unsubscribe, campaign CRUD, send-now, scheduling, preview, and dashboard stats. Frontend (separate repo) is in progress.

## Local setup

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
php artisan serve
```
