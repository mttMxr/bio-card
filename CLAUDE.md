# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

LinkStack is a self-hosted, open-source Linktree alternative built with Laravel 9. It provides a customizable link-sharing platform where users can create personal pages with multiple links, similar to Linktree, with complete control over their data and hosting.

## Tech Stack

- **Backend**: Laravel 9 (PHP 8.0+)
- **Frontend**: Livewire 2, Alpine.js, Tailwind CSS 2
- **Build Tool**: Laravel Mix (npm-based)
- **Database**: SQLite (default) or MySQL
- **Key Dependencies**:
  - Spatie Laravel Backup for backup functionality
  - Laravel Socialite for OAuth authentication
  - QR code generation (simplesoftwareio/simple-qrcode)
  - vCard generation (jeroendesloovere/vcard)

## Development Commands

### Initial Setup
```bash
composer update -vvv
php artisan migrate
php artisan db:seed
php artisan db:seed --class="AdminSeeder"
php artisan db:seed --class="PageSeeder"
php artisan db:seed --class="ButtonSeeder"
```

Default seeded credentials: `admin@admin.com` / `12345678`

### Daily Development
```bash
# Start development server
php artisan serve

# Asset compilation
npm run dev          # Development build
npm run watch        # Watch mode
npm run production   # Production build

# Database operations
php artisan migrate           # Run migrations
php artisan migrate:fresh     # Fresh migration (drops all tables)
php artisan db:seed          # Seed database

# Laravel commands
php artisan cache:clear      # Clear application cache
php artisan config:clear     # Clear config cache
php artisan route:list       # List all routes
php artisan tinker          # Interactive shell
```

### Testing
```bash
# Run all tests
php artisan test
# or
vendor/bin/phpunit

# Run specific test suite
vendor/bin/phpunit --testsuite=Unit
vendor/bin/phpunit --testsuite=Feature
```

Note: The `/tests` directory may not exist yet - tests can be created using `php artisan make:test TestName`

## Architecture & Code Organization

### Route Structure

Routes are modularized across multiple files in `/routes`:
- `web.php` - Main routing logic with installer detection and maintenance mode handling
- `home.php` - Home/public-facing routes
- `auth.php` - Authentication routes
- `api.php` - API endpoints (if any)

**Critical routing logic**: The app checks for installer state and maintenance mode at startup:
- Installation mode: Triggered by `INSTALLING` or `INSTALLERLOCK` files in base directory
- Maintenance mode: Controlled by `MAINTENANCE_MODE` env variable
- Auto-generates `APP_KEY` if missing on first load

### Controller Organization

Main controllers in `/app/Http/Controllers`:
- `AdminController.php` - Admin panel operations, configuration, user management
- `UserController.php` - User-facing operations (profile pages, link clicks, themes)
- `HomeController.php` - Public homepage and demo page
- `InstallerController.php` - First-time setup wizard
- `LinkTypeViewController.php` - Link type rendering logic
- `/Admin/*` - Nested admin-specific controllers

### Models

Core models in `/app/Models`:
- `User.php` - User accounts with littlelink_name (username for @username URLs)
- `Link.php` - Individual links/buttons on user pages
- `Button.php` - Available button types/templates
- `Page.php` - Site-wide page settings and content
- `UserData.php` - Additional user metadata
- `LinkType.php` - Link type definitions

### Blocks System

Custom page blocks in `/blocks` directory:
- `heading/` - Heading blocks
- `link/` - Link/button blocks
- `text/` - Text content blocks
- `spacer/` - Spacing blocks
- `vcard/` - vCard download blocks
- `email/`, `telephone/` - Contact blocks

Each block is a self-contained component with its own views and logic.

### Helper Functions

Global helper functions defined in `/app/Functions/functions.php` (autoloaded via composer.json):
- `findFile()`, `findAvatar()`, `findBackground()` - Asset discovery utilities
- `analyzeImageBrightness()` - Image analysis for theme adjustments
- Custom functions for theme management, configuration, etc.

### Livewire Components

Livewire used for interactive components (minimal usage):
- Service provider: `app/Providers/LivewireServiceProvider.php`
- Components: `app/Http/Livewire/`
- User table management via `UserTable.php`

### Views

Blade templates in `/resources/views`:
- `/admin` - Admin panel views
- `/studio` - User studio/editor views
- `/panel` - User panel views
- `/linkstack` - Public link page templates
- `/layouts` - Shared layouts
- `/components` - Reusable components
- `/installer` - Setup wizard views

### URL Pattern

Public user pages accessible via:
- `/@username` - Primary pattern for user pages
- `/custom_prefix/username` - If custom URL prefix configured
- `/going/{id}` - Click tracking redirect
- `/theme/@username` - Theme preview

### Configuration

- Advanced configuration stored in `/config/advanced-config.php` (copied from template in `/storage/templates/`)
- Environment configuration via `.env` (see `.env.example`)
- Supports dynamic locale configuration via `LOCALES` env variable

## Important Implementation Details

### Installer Logic
- Installer routes activate when `INSTALLING` or `INSTALLERLOCK` files exist
- After successful installation, `storage/app/ISINSTALLED` is created
- Installer creates admin user, configures database, and sets up initial config

### Theme System
- Themes are uploadable via admin panel
- Custom themes stored and managed through theme endpoints
- Theme customization per user via `theme/@username` route

### Asset Management
- User avatars: `/assets/img`
- Backgrounds: `/assets/img/background-img/`
- LinkStack images: `/assets/linkstack/images/`

### Database
- Default: SQLite (portable, single-file database)
- Alternative: MySQL (configured via `.env`)
- Migrations in `/database/migrations`
- Seeders provide default buttons, pages, and admin user

### Auto-Update System
- Built-in updater accessible from admin panel
- Creates backups in `/backups/updater-backups` before updating
- Note: Database backups only for SQLite (MySQL requires manual backup)

## Project-Specific Conventions

### Branch Strategy
- Main branch: `main`
- Development happens on `beta` branch (see recent commits)
- Feature branches should be created from appropriate base branch

### Code Style
- Follow Laravel conventions
- PSR-4 autoloading for `App\` namespace
- Blade templating for views
- Eloquent ORM for database operations

### When Adding Features
- Check existing button types in database before creating new ones
- Follow the blocks pattern for new page element types
- Use existing helper functions in `functions.php` where applicable
- Admin features go through AdminController
- User-facing features through UserController

### Maintenance Mode
- Set `MAINTENANCE_MODE=true` in `.env` to disable routes
- Bypasses all routing during maintenance

## Running Locally

Per the user's request (запусти локально / "run locally"):

```bash
# 1. Install PHP dependencies
composer install

# 2. Install Node dependencies
npm install

# 3. Copy environment file
cp .env.example .env

# 4. Generate application key
php artisan key:generate

# 5. Run migrations and seeders
php artisan migrate
php artisan db:seed --class="AdminSeeder"
php artisan db:seed --class="PageSeeder"
php artisan db:seed --class="ButtonSeeder"

# 6. Compile assets
npm run dev

# 7. Start development server
php artisan serve
```

The application will be available at `http://localhost:8000`

Login with: `admin@admin.com` / `12345678`

## Resources

- Main documentation: https://linkstack.org/docs
- Themes: https://linkstack.org/themes
- Discord community: https://discord.linkstack.org
- Docker version: https://github.com/LinkStackOrg/linkstack-docker
