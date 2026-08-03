# MENRO Tree Planting Monitoring API

Laravel API for the MENRO Tagoloan tree planting monitoring portal (`api-menro`).

## Production security checklist

Before exposing this API beyond local development:

1. Set `APP_ENV=production`, `APP_DEBUG=false`, and `LOG_LEVEL=error`.
2. Use HTTPS for `APP_URL`, `FRONTEND_URL`, and `MOBILE_URL`.
3. Keep a strong unique `APP_KEY` (never commit `.env`).
4. Set `GOOGLE_ALLOWED_EMAILS` and/or `GOOGLE_ALLOWED_DOMAINS` (new Google sign-ups fail closed when both are empty outside local).
5. Set `SANCTUM_EXPIRATION` (minutes; default 10080 = 7 days).
6. Set `TRUSTED_PROXIES` to your load balancer / reverse-proxy IPs (avoid `*` in production).
7. Rotate Google OAuth client secret, mail password, and `OCR_SPACE_API_KEY` if they may have been shared.
8. Run `php artisan storage:migrate-public-to-private` once after deploying this hardening so existing public uploads move to the private disk.
9. Do not run demo seeders (`php artisan db:seed`) in production — the seeder refuses when `APP_ENV=production`.

Web admin self-registration requires email OTP, then the account is Active immediately. Field users still need managing-admin approval after OTP.

## Local setup

Copy `.env.example` to `.env`, generate an app key, migrate, and serve as usual for Laravel.

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```
