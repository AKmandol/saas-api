# Multi-Tenant SaaS Application

A multi-tenant SaaS application built with Laravel 13, MySQL, Sanctum, Spatie Permission, Redis, database queues, and Vue 3. The system supports company-based tenant isolation, RBAC, subscription-based feature limits, customer/user management, dashboard analytics, caching, rate limiting, and background jobs.

---

## 1. Simple Business Logic

Each registered user belongs to a company, and each company has its own users, customers, and subscription.

The application uses a shared database with `company_id` to isolate tenant data. Users can only access resources belonging to their own company.

Subscription plans define limits such as the maximum number of customers and users. Before creating a customer or user, the system checks the company's active subscription and rejects the operation when the limit is reached.

Authentication is handled using Laravel Sanctum, while Spatie Permission manages roles and permissions.

---

## 2. Setup Instructions

### Requirements

* PHP 8.3+
* Composer
* Node.js / NPM
* MySQL
* Redis
* Git

### Install Backend

Clone the repository:

```bash
git clone <repository-url>
cd <project-directory>
```

Install PHP dependencies:

```bash
composer install
```

### Install Frontend Dependencies

```bash
npm install
```

### Environment Configuration

Copy `.env.example` to `.env`:

```bash
cp .env.example .env
```

Generate the application key:

```bash
php artisan key:generate
```

Configure MySQL in `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=saas
DB_USERNAME=root
DB_PASSWORD=
```

Configure Redis:

```env
CACHE_STORE=redis

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

Configure the queue:

```env
QUEUE_CONNECTION=database
```

### Database Setup

Run migrations and seed the database:

```bash
php artisan migrate --seed
```

The seeders create:

* Subscription plans
* Plan features
* Roles
* Permissions
* Companies
* Users
* Subscriptions
* Customers

Seeder User:

```bash
admin@acme.test
```

```bash
manager@acme.test
```

```bash
user@acme.test
```

Seeder All tenant Password:

```bash
password
```

creates the default plans, roles, permissions, companies, users, subscriptions, and demo customers required to test the application.

Default Roles
owner
admin
manager
user
Default Permissions
analytics.view

company.view
company.update

customers.view
customers.create
customers.update
customers.delete

users.view
users.create
users.update
users.delete

subscription.view
subscription.update
Permission Assignment

Permissions are assigned to roles through the RolePermissionSeeder.

The general permission structure is:

owner
 └── Full company access

admin
 └── Company, users, customers and subscription management

manager
 └── Operational access based on assigned permissions

user
 └── Limited application access

The exact role-to-permission mapping is defined in:

database/seeders/RolePermissionSeeder.php
Default Subscription Plans

The seeders create the following plans:

Plan	Customer Limit	User Limit
Free	100	5
Pro	1,000	20
Business	10,000	100

Feature limits are stored in:

plan_features
Demo Users

The UserSeeder creates users for testing different roles and companies.

Example seeded accounts:

Email	Role	Company
owner@acme.test	owner	Acme Technologies
admin@acme.test	admin	Acme Technologies
manager@acme.test	manager	Acme Technologies
user@acme.test	user	Acme Technologies
owner@global.test	owner	Global Solutions Ltd
admin@global.test	admin	Global Solutions Ltd
owner@startup.test	owner	Startup Labs

Use the password configured in UserSeeder.php when testing the seeded accounts.


### Run Application

Start Laravel:

```bash
php artisan serve
```

Start Vue/Vite:

```bash
npm run dev
```

Start the queue worker:

```bash
php artisan queue:work
```

The application requires Laravel, Vite, Redis, MySQL, and the queue worker to be running as appropriate for the enabled features.

### Production Frontend Build

```bash
npm run build
```

---

## 3. Testing Instructions

The project uses Laravel's PHPUnit testing framework.

Run all tests:

```bash
php artisan test
```

Run a specific test suite:

```bash
php artisan test --filter=AuthTest
```

```bash
php artisan test --filter=CustomerTest
```

```bash
php artisan test --filter=UserTest
```

```bash
php artisan test --filter=SubscriptionTest
```

```bash
php artisan test --filter=DashboardTest
```

```bash
php artisan test --filter=QueueTest
```

### Testing Environment

Tests use a separate `.env.testing` environment with an in-memory SQLite database:

```env
APP_ENV=testing

DB_CONNECTION=sqlite
DB_DATABASE=:memory:

CACHE_STORE=array
QUEUE_CONNECTION=sync
SESSION_DRIVER=array
```

Tests use Laravel's `RefreshDatabase` trait to reset the database between tests.

### Main Test Coverage

The test suite covers:

* Registration and login
* Sanctum authentication
* Logout
* Validation
* RBAC and permissions
* Tenant isolation
* Customer CRUD
* User CRUD
* Subscription changes
* Subscription limits
* Dashboard analytics
* Queue jobs
* Cache-related behavior

---

## 4. Database Schema and Architecture Explanation

### Database Architecture

The application uses a **shared database, shared schema multi-tenant architecture**.

The main relationship is:

```text
                         ┌──────────────┐
                         │   companies  │
                         └──────┬───────┘
                                │
              ┌─────────────────┼─────────────────┐
              │                 │                 │
              ▼                 ▼                 ▼
         ┌─────────┐      ┌───────────┐    ┌──────────────┐
         │  users  │      │ customers │    │subscriptions │
         └─────────┘      └───────────┘    └───────┬──────┘
                                                   │
                                                   ▼
                                             ┌──────────┐
                                             │  plans   │
                                             └────┬─────┘
                                                  │
                                                  ▼
                                           ┌──────────────┐
                                           │ plan_features│
                                           └──────────────┘
```

### Main Tables

#### `companies`

Stores tenant/company information.

```text
id
name
slug
email
status
created_at
updated_at
```

#### `users`

Stores users belonging to companies.

```text
id
company_id
name
email
password
created_at
updated_at
```

The combination of:

```text
company_id + email
```

is unique.

#### `customers`

Stores customers belonging to a company.

```text
id
company_id
name
email
phone
status
created_at
updated_at
```

Customer email is unique within each company.

#### `plans`

Stores subscription plans.

```text
id
name
slug
created_at
updated_at
```

#### `plan_features`

Stores limits for each subscription plan.

Example:

```text
plan_id | feature    | limit
--------|------------|------
1       | customers  | 100
1       | users      | 5
2       | customers  | 1000
2       | users      | 20
```

#### `subscriptions`

Connects a company with its current subscription plan.

```text
id
company_id
plan_id
status
starts_at
ends_at
created_at
updated_at
```

### Tenant Isolation

Every tenant-owned query uses the authenticated user's `company_id`.

Example:

```php
Customer::query()
    ->where('company_id', $request->user()->company_id)
    ->findOrFail($customerId);
```

Therefore:

```text
Company A → Company A resources ✓
Company A → Company B resources ✗
```

Cross-company resources return `404`.

### RBAC Tables

Spatie Permission manages:

```text
roles
permissions
model_has_roles
model_has_permissions
role_has_permissions
```

---

## 5. API Documentation with Sample Requests and Responses

### Base URL

```text
/api/v1
```

Protected requests require:

```http
Authorization: Bearer {token}
Accept: application/json
```

### Register

```http
POST /api/v1/auth/register
```

Request:

```json
{
    "company_name": "Example Company",
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

Response:

```json
{
    "message": "Registration successful.",
    "data": {
        "company": {},
        "user": {},
        "token": "1|xxxxxxxxxxxxxxxx"
    }
}
```

### Login

```http
POST /api/v1/auth/login
```

Request:

```json
{
    "email": "john@example.com",
    "password": "password123"
}
```

Response:

```json
{
    "message": "Login successful.",
    "data": {
        "user": {},
        "roles": [
            "owner"
        ],
        "permissions": [
            "customers.view",
            "customers.create"
        ],
        "token": "1|xxxxxxxxxxxxxxxx"
    }
}
```

### Get Dashboard

```http
GET /api/v1/dashboard
```

Response:

```json
{
    "data": {
        "customers": {
            "total": 10,
            "active": 8,
            "inactive": 2
        },
        "users": {
            "total": 4
        },
        "subscription": {
            "plan": "Pro",
            "status": "active"
        }
    }
}
```

### Create Customer

```http
POST /api/v1/customers
```

Request:

```json
{
    "name": "John Customer",
    "email": "john@example.com",
    "phone": "+8801800000011",
    "status": "active"
}
```

Response:

```json
{
    "data": {
        "id": 1,
        "name": "John Customer",
        "email": "john@example.com",
        "phone": "+8801800000011",
        "status": "active"
    }
}
```

### List Customers

```http
GET /api/v1/customers?search=john&status=active&per_page=20
```

Response:

```json
{
    "data": [
        {
            "id": 1,
            "name": "John Customer",
            "email": "john@example.com",
            "phone": "+8801800000011",
            "status": "active"
        }
    ],
    "links": {},
    "meta": {}
}
```

### Create User

```http
POST /api/v1/users
```

Request:

```json
{
    "name": "Jane Doe",
    "email": "jane@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "role": "manager"
}
```

### Get Company

```http
GET /api/v1/company
```

Response:

```json
{
    "data": {
        "id": 1,
        "name": "Example Company",
        "slug": "example-company",
        "status": "active"
    }
}
```

### Get Subscription

```http
GET /api/v1/subscription
```

Response:

```json
{
    "data": {
        "id": 1,
        "status": "active",
        "plan": {
            "id": 2,
            "name": "Pro",
            "slug": "pro"
        },
        "features": [
            {
                "feature": "customers",
                "limit": 1000
            },
            {
                "feature": "users",
                "limit": 20
            }
        ]
    }
}
```

### API Endpoint Summary

| Method    | Endpoint                |
| --------- | ----------------------- |
| POST      | `/auth/register`        |
| POST      | `/auth/login`           |
| POST      | `/auth/logout`          |
| GET       | `/auth/me`              |
| GET       | `/dashboard`            |
| GET       | `/customers`            |
| POST      | `/customers`            |
| GET       | `/customers/{customer}` |
| PUT/PATCH | `/customers/{customer}` |
| DELETE    | `/customers/{customer}` |
| GET       | `/users`                |
| POST      | `/users`                |
| GET       | `/users/{user}`         |
| PUT/PATCH | `/users/{user}`         |
| DELETE    | `/users/{user}`         |
| GET       | `/company`              |
| PUT/PATCH | `/company`              |
| GET       | `/subscription`         |
| GET       | `/plans`                |
| PUT       | `/subscription`         |

For the complete endpoint reference and generated documentation, see `API_DOCUMENTATION.md` and Scribe documentation.

---

## 6. Caching Strategy and Invalidation

Redis is used to cache dashboard analytics because dashboard statistics can require multiple database queries.

The dashboard cache key is tenant-specific:

```text
company:{company_id}:dashboard
```

The cache lifetime is 5 minutes:

```php
Cache::remember(
    $cacheKey,
    now()->addMinutes(5),
    fn () => $this->buildDashboard($company)
);
```

### Why Tenant-Specific Keys?

Using the company ID in the cache key prevents one company's dashboard from being returned to another company.

```text
company:1:dashboard
company:2:dashboard
company:3:dashboard
```

Each tenant has an independent cache entry.

### Cache Invalidation

Dashboard cache is invalidated when data affecting dashboard statistics changes.

For example:

```text
Customer Created
      ↓
Invalidate company dashboard cache

Customer Updated
      ↓
Invalidate company dashboard cache

Customer Deleted
      ↓
Invalidate company dashboard cache

User Created/Updated/Deleted
      ↓
Invalidate company dashboard cache
```

The application uses a dedicated `DashboardCacheService` to generate and invalidate cache keys.

---

## 7. Database Optimization and Indexing

Database performance is addressed through proper indexing, tenant-scoped queries, pagination, and avoiding unnecessary data loading.

### Composite Unique Indexes

Users:

```text
UNIQUE(company_id, email)
```

Customers:

```text
UNIQUE(company_id, email)
```

These indexes provide both data integrity and efficient company-scoped email lookups.

### Customer Status Index

Customers use:

```text
INDEX(company_id, status)
```

This supports queries such as:

```sql
SELECT *
FROM customers
WHERE company_id = ?
AND status = 'active';
```

### Subscription Index

Subscriptions use:

```text
INDEX(company_id, status)
```

This supports efficient active-subscription lookups.

### Tenant-Scoped Queries

Queries are always scoped by `company_id`.

Example:

```php
Customer::query()
    ->where('company_id', $companyId)
    ->latest('id')
    ->paginate($perPage);
```

This reduces the dataset being searched and maintains tenant isolation.

### Pagination

Large datasets are not returned in a single response.

Instead:

```php
->paginate($perPage)
```

is used to limit the number of records retrieved per request.

### Eager Loading

Relationships that are required by the response are eager loaded to avoid N+1 queries.

Example:

```php
User::query()
    ->with('roles')
    ->where('company_id', $companyId)
    ->paginate();
```

### Search

Customer search supports:

```text
name
email
phone
```

For large-scale production datasets, search indexes or a dedicated search engine could be introduced if `%term%` searches become a performance bottleneck.

---

## 8. System Design and Key Technical Decisions

### High-Level Architecture

```text
                    ┌──────────────────┐
                    │    Vue 3 SPA     │
                    └────────┬─────────┘
                             │
                             ▼
                    ┌──────────────────┐
                    │ Laravel REST API │
                    │    /api/v1       │
                    └────────┬─────────┘
                             │
          ┌──────────────────┼──────────────────┐
          │                  │                  │
          ▼                  ▼                  ▼
     Authentication         RBAC          Tenant Context
       Sanctum            Spatie          company_id
          │                  │                  │
          └──────────────────┼──────────────────┘
                             │
                ┌────────────┼────────────┐
                ▼            ▼            ▼
              MySQL        Redis       Queue
                           Cache       Worker
```

### Shared Database Multi-Tenancy

A shared database was selected instead of a separate database per tenant.

The main reason is simplicity and suitability for the application's current scale.

Tenant isolation is enforced through:

```text
company_id
```

at the database query and application layers.

### Service Layer

Business logic such as subscription checks is kept in services rather than being duplicated across controllers.

Example:

```text
SubscriptionService
```

is responsible for subscription and feature-limit logic.

This makes the business rules reusable and easier to test.

### Form Requests

Validation and request authorization are handled using dedicated Form Request classes.

Examples:

```text
StoreCustomerRequest
UpdateCustomerRequest
StoreUserRequest
UpdateUserRequest
UpdateCompanyRequest
```

This keeps controllers focused on coordinating the request and response.

### API Resources

API Resources are used to control the structure of API responses.

Examples:

```text
CustomerResource
UserResource
CompanyResource
SubscriptionResource
```

This prevents exposing unnecessary model attributes and keeps API responses consistent.

### Transactions

Database transactions are used for operations involving multiple related writes.

Registration is an example:

```text
Create Company
      ↓
Create User
      ↓
Create Subscription
      ↓
Assign Role
      ↓
Commit
```

If a step fails, the transaction rolls back.

Subscription updates also use transactions and locking to reduce concurrent update problems.

### Authentication

Laravel Sanctum was selected for API authentication because the application uses token-based API access.

Protected routes use:

```text
auth:sanctum
```

### Authorization

Spatie Permission provides role and permission management.

This separates:

```text
Authentication → Who is the user?
Authorization  → What can the user do?
```

### Background Processing

Non-critical asynchronous work is handled through Laravel queues.

The database queue driver is used for the current implementation.

Example:

```text
User Registration
      ↓
Transaction Commit
      ↓
Queue Job
      ↓
Welcome Email Processing
```

### Rate Limiting

API rate limiting protects the system from excessive requests.

Current limits:

```text
Authentication: 5 requests/minute
Authenticated API: 60 requests/minute
```

### API Versioning

All API endpoints are placed under:

```text
/api/v1
```

This allows future versions to be introduced without immediately breaking existing clients.

### Key Design Principles

The implementation follows these principles:

* Separation of concerns
* Single responsibility
* Reusable business services
* Request-level validation
* Explicit tenant isolation
* Database integrity through constraints
* Transactional consistency
* Least-privilege authorization
* Cache only expensive/read-heavy operations
* Pagination for collection endpoints
* Versioned APIs
* Automated testing
