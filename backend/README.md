# Laravel Backend (API)

This folder contains a Laravel-oriented backend structure for Vision Board Maker.

## Setup (when network/dependencies are available)

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

## API routes

- `GET /api/health`
- `GET /api/presets`
- `POST /api/boards`
