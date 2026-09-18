# Business Logic

The application uses a **shared database multi-tenant architecture**.

Each company has its own users, customers, and subscription. Tenant-owned records contain a `company_id`.

```text
Company
 ├── Users
 ├── Customers
 └── Subscription
       └── Plan
            └── Plan Features
```

# Installation

## Requirements

Make sure the following are installed:

```text
PHP >= 8.3
Composer
Node.js
NPM
MySQL
Redis
Git
```

## 1. Clone the Repository

```bash
git clone <repository-url>

cd <project-directory>
```

## 2. Install PHP Dependencies

```bash
composer install
```

## 3. Install Frontend Dependencies

```bash
npm install
```

## 4. Configure Environment

Copy the example environment file:

```bash
cp .env.example .env
```

On Windows, copy:

```text
.env.example
```

to:

```text
.env
```

## 5. Generate Application Key

```bash
php artisan key:generate
```

## 6. Configure Database

Update `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=saas
DB_USERNAME=root
DB_PASSWORD=
```

Create the database before running migrations.

Example:

```sql
CREATE DATABASE saas;
```

## 7. Configure Redis

Update `.env`:

```env
CACHE_STORE=redis

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

Make sure Redis is running.

## 8. Configure Queue

The project uses Laravel's database queue:

```env
QUEUE_CONNECTION=database
```

The queue migration should be included with the project migrations.

If required:

```bash
php artisan queue:table
php artisan migrate
```

## 9. Run Migrations and Seeders

```bash
php artisan migrate --seed
```

The seeders create the initial:

* Plans
* Plan features
* Roles
* Permissions
* Companies
* Users
* Subscriptions
* Customers

## 10. Start Laravel

```bash
php artisan serve
```

The API will normally be available at:

```text
http://127.0.0.1:8000
```

## 11. Start Vue/Vite

In another terminal:

```bash
npm run dev
```

## 12. Start Queue Worker

In another terminal:

```bash
php artisan queue:work
```

The application now has:

```text
Laravel API
Vue Frontend
Redis
Database
Queue Worker
```

running together.

---

# Testing

The application uses Laravel's PHPUnit testing framework.

## Run All Tests

```bash
php artisan test
```

or:

```bash
vendor/bin/phpunit
```

## Run a Specific Test

Authentication:

```bash
php artisan test --filter=AuthTest
```

Customers:

```bash
php artisan test --filter=CustomerTest
```

Users:

```bash
php artisan test --filter=UserTest
```

Subscriptions:

```bash
php artisan test --filter=SubscriptionTest
```

Dashboard:

```bash
php artisan test --filter=DashboardTest
```

Queue:

```bash
php artisan test --filter=QueueTest
```

---

## Test Database

Automated tests use a separate testing environment.

Example `.env.testing`:

```env
APP_ENV=testing

DB_CONNECTION=sqlite
DB_DATABASE=:memory:

CACHE_STORE=array
QUEUE_CONNECTION=sync
SESSION_DRIVER=array
```
