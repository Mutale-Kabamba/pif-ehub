# PIF E-Hub Laravel Application

Play It Forward (PIF) E-Hub — A Laravel-based digital skills training management platform with anonymous trainee surveys, candidate literacy assessment, panel interview scoring, and a live selection leaderboard.

## Features

- **Anonymous Student Survey Portal** — Baseline (Day 1) and Endline (Day 156) self-assessment surveys with quantitative Likert-scale questions and qualitative open-ended responses
- **Text-to-Speech Introduction** — Web Speech API integration for accessibility
- **Admin Authentication** — Role-based access control for Super User and 5 Panelists
- **Live Selection Leaderboard** — Aggregates literacy scores (/20) and averaged panel interview scores (/20) for a ranked grand total (/40)
- **Survey Analytics** — Chart.js visualizations comparing baseline vs endline responses
- **Literacy Assessment Terminal** — Score candidates on 10 practical computer literacy tasks (0-2 each, max 20)
- **Panel Interview Evaluation** — 4-criteria interview scoring (1-5 each, max 20) with per-panelist submissions
- **CSV Export** — Export leaderboard data for offline processing

## Default Login Credentials

| Role | Email / Selection | Password |
|------|-------------------|----------|
| Super User | "Super User" | `PIF_Admin_2026` |
| Rabecca | "Rabecca" | `PIF_Rab_2026` |
| Sarah | "Sarah" | `PIF_Sar_2026` |
| Bracious | "Bracious" | `PIF_Bra_2026` |
| Mutale | "Mutale" | `PIF_Mut_2026` |
| Blessing | "Blessing" | `PIF_Ble_2026` |

## Requirements

- PHP 8.2+
- SQLite (default) or MySQL/PostgreSQL
- Composer

## Installation

### Quick Start (Recommended)

Run the provided setup script which handles all edge cases automatically:

```bash
git clone <repository-url> pif-ehub
cd pif-ehub
chmod +x setup.sh
./setup.sh
```

### Manual Installation

If you prefer manual setup, follow these steps exactly:

```bash
# 1. Clone the repository
git clone <repository-url> pif-ehub
cd pif-ehub

# 2. CRITICAL: Create bootstrap/cache directory BEFORE composer install
# This prevents the common "post-autoload-dump returned with error code 1" error
mkdir -p bootstrap/cache
echo -e "*\n!.gitignore" > bootstrap/cache/.gitignore

# 3. Create environment file
cp .env.example .env

# 4. Create SQLite database file
touch database/database.sqlite

# 5. Install dependencies (no scripts first to avoid bootstrap issues)
composer install --no-dev --optimize-autoloader --no-scripts

# 6. Generate application key
php artisan key:generate

# 7. Now run the post-autoload scripts
composer dump-autoload --optimize

# 8. Run migrations and seeders
php artisan migrate --force
php artisan db:seed --force
```

### Fixing "post-autoload-dump returned with error code 1"

If you already ran `composer install` and got this error, run these commands to fix it:

```bash
# Step 1: Create the missing bootstrap/cache directory
mkdir -p bootstrap/cache

# Step 2: Ensure SQLite database exists
touch database/database.sqlite

# Step 3: Generate app key (if .env is missing it)
php artisan key:generate

# Step 4: Re-run the autoload scripts
composer dump-autoload --optimize
```

## Local Development

```bash
php artisan serve
```

Visit `http://localhost:8000` for the survey portal.
Visit `http://localhost:8000/admin/login` for the admin dashboard.

## Laravel Cloud Deployment

### 1. Environment Variables

Set these environment variables in your Laravel Cloud dashboard:

```
APP_ENV=production
APP_DEBUG=false
APP_KEY=<generate with php artisan key:generate --show>
DB_CONNECTION=pgsql          # Laravel Cloud default
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
```

### 2. Build Commands

Laravel Cloud will automatically run:

```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan db:seed --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 3. Database

By default, the app uses SQLite (zero-config). For Laravel Cloud production, switch to PostgreSQL:

1. Provision a PostgreSQL database in Laravel Cloud
2. Set `DB_CONNECTION=pgsql`
3. The migrations and seeders will handle the rest

### 4. Web Root

Point your web server to the `/public` directory. The `public/index.php` is the application entry point.

## Project Structure

```
pif-ehub/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AdminController.php         # Auth, dashboard, analytics, literacy
│   │   │   ├── LeaderboardController.php    # Score aggregation & ranking
│   │   │   ├── PanelistController.php       # Interview evaluation
│   │   │   └── SurveyController.php         # Public survey portal
│   │   └── Middleware/
│   │       ├── Authenticate.php             # Session-based auth
│   │       └── RoleMiddleware.php           # Role-based access control
│   └── Models/
│       ├── Candidate.php                    # 20 seeded candidates
│       ├── LiteracyScore.php                # 10-task literacy scores
│       ├── PanelScore.php                   # 4-criteria interview scores
│       ├── SurveyResponse.php               # Anonymous survey data
│       └── User.php                         # Super + 5 Panelists
├── database/
│   ├── migrations/                          # All table schemas
│   └── seeders/
│       ├── CandidateSeeder.php              # 20 candidate names
│       ├── DatabaseSeeder.php               # Orchestrator
│       └── UserSeeder.php                   # Super + 5 Panelists
├── resources/
│   └── views/
│       ├── layouts/app.blade.php            # Main layout with sidebar
│       ├── survey/index.blade.php           # Student survey portal
│       ├── admin/
│       │   ├── login.blade.php              # Admin login
│       │   ├── dashboard.blade.php          # Tabbed dashboard
│       │   └── partials/
│       │       ├── leaderboard.blade.php    # Ranked candidate table
│       │       ├── analytics.blade.php      # Chart.js metrics
│       │       ├── literacy-form.blade.php  # Literacy grading
│       │       └── panel-form.blade.php     # Interview evaluation
├── public/
│   ├── css/pif-theme.css                    # Responsive PIF theme
│   ├── js/app.js                            # TTS, sidebar, charts
│   └── index.php                            # Entry point
└── routes/web.php                           # All application routes
```

## Database Schema

### survey_responses
- Anonymous survey submissions with 11 quantitative (1-5) and 4 qualitative fields
- `survey_type`: baseline or endline

### candidates
- 20 pre-seeded trainee candidates

### literacy_scores
- Per-candidate scores on 10 tasks (0-2 each, max 20)
- `total_score`: auto-computed sum

### panel_scores
- Per-candidate, per-panelist interview scores
- 4 criteria (1-5 each, max 20)
- `comments`: optional interviewer notes

### users
- 1 Super User with full access
- 5 Panelists with interview scoring access only

## License

Proprietary — Play It Forward Zambia.
