# DOST FMS Laravel API

## Requirements

- PHP 8.2 or newer
- Composer
- MySQL (XAMPP is suitable on Windows)

## First-time setup

Open this `backend` folder in VS Code, then run these commands in its terminal:

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
php artisan jwt:secret
php artisan migrate:fresh --seed
php artisan serve
```

The API will be available at `http://127.0.0.1:8000/api`.

Before running the migration, start MySQL in XAMPP and create an empty database named `dost_fms` in phpMyAdmin. If your MySQL username or password differs, update `DB_USERNAME` and `DB_PASSWORD` in `.env`.

## Test login

Send `POST http://127.0.0.1:8000/api/auth/login` with JSON:

```json
{
  "email": "YOUR_TEST_USER_EMAIL",
  "password": "YOUR_TEST_USER_PASSWORD"
}
```

Use a test account created for your local development database. Do not commit real credentials.

For protected requests, add the header `Authorization: Bearer YOUR_TOKEN`.
