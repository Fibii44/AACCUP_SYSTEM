# AACCUP Multi-Tenant Platform

A multi-tenant Laravel application for AACCUP departments.

## Utility Scripts

The following utility scripts are provided to help manage tenants:

### `direct-tenant-tables.php`

Creates or fixes tables in a tenant's database.

```bash
php direct-tenant-tables.php <tenant-id>
```

### `check-tenant-domain.php`

Verifies if a tenant domain is properly set up.

```bash
php check-tenant-domain.php <tenant-id>
```

### `add-tenant-host.php`

Adds tenant domains to your local hosts file. Run the generated batch file as administrator.

```bash
php add-tenant-host.php
```

### `configure-herd-domains.php`

Configures Laravel Herd for multi-tenancy (if you're using Herd).

```bash
php configure-herd-domains.php
```

## Routes

The application uses two main route groups:

### Central Domain Routes

Routes defined for the main application domain (aaccup.test):

```php
Route::domain(config('app.url'))->group(function () {
    // Central application routes
});
```

These routes handle:
- Public landing pages for the main site
- Tenant registration process
- Admin dashboard for managing tenants
- Tenant approval process

### Tenant Routes

Routes for tenant subdomains (e.g., department.aaccup.test):

```php
Route::middleware(['web', InitializeTenancyByDomain::class])->group(function () {
    // Tenant-specific routes
});
```

These routes:
- Are automatically isolated to the tenant's context
- Connect to the tenant's database
- Provide tenant-specific authentication
- Allow tenant admins to customize their own experience

## Authentication

- **Central Auth**: Uses the central database to authenticate admin users
- **Tenant Auth**: Uses the tenant's database for tenant-specific users
- Tenant auth is automatically configured using `AppServiceProvider`

## Database Structure

- **Central Database**: Contains tenants, domains, and global data
- **Tenant Databases**: Each tenant has their own isolated database (tenant_*)
  - When a tenant is approved, their database is automatically created
  - Each tenant database includes its own users table

## Customization

Each tenant can customize their:
- Landing page colors and content
- Logo and branding
- And more

These settings are stored in the `tenant_settings` table in each tenant's database. 