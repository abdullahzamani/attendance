# Staff Movement System

A rebuild of the 2016 PHP project [`projectlearning`](https://github.com/abdullahzamani/projectlearning)
(*Sistem Pergerakan dan Keberadaan Pegawai di Pejabat*): record when staff leave the office and come
back, see at a glance who is out and why, and pull daily, monthly or yearly reports.

Built with **Laravel 13**, **Filament 5** (admin panel) and **Livewire** (live display board).

## Features

- **Check out / check in.** Staff are checked out with a reason, an optional expected-return time
  and a note, from the dashboard, the staff list or the movement log, then checked back in with one
  click. Someone already out can't be checked out again.
- **Dashboard.** Live counts (in office, out now, overdue, check-outs today) and the list of
  everyone currently out, refreshed every 30 seconds.
- **Display board** (`/board`). A wall-screen view of every unit's staff with green/red/amber
  status, refreshed every 15 seconds. Use `/board?unit=UGT` to show one unit.
- **Movement log.** Today / This month / This year / All tabs replace the old daily, monthly and
  yearly reports. You can filter by unit, reason, staff, status and date range, and
  **Export CSV** downloads exactly what the table shows.
- **Staff, units and movement reasons**, managed from the panel.
- **Roles.** *Administrators* manage users, units and reasons and can correct or delete history.
  *Clerks* record movements and manage staff.

## Getting started

Requirements: PHP 8.3+ (with `intl`, `pdo_sqlite` or `pdo_mysql`), Composer, Node.js 20+.

```sh
git clone https://github.com/abdullahzamani/attendance.git
cd attendance
composer setup          # installs, creates .env + SQLite database, migrates, seeds
php artisan serve
```

Open http://localhost:8000 and sign in with `admin@example.com` / `password`
(set `ADMIN_EMAIL` and `ADMIN_PASSWORD` in `.env` **before** seeding to choose your own).
Change the password straight away on a real installation.

To try it with sample staff and movement history:

```sh
php artisan migrate:fresh --seeder=DemoSeeder
```

### Using MySQL instead of SQLite

In `.env`:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=attendance
DB_USERNAME=attendance
DB_PASSWORD=a-strong-password
```

then `php artisan migrate --seed`.

### Time zone

Times are stored and shown in `APP_TIMEZONE` (default `Asia/Kuala_Lumpur`).

## Project layout

| Path | What lives there |
|---|---|
| `app/Models` | `Staff`, `Unit`, `MovementReason`, `Movement` (one trip out: `out_at` → `in_at`), `User` |
| `app/Actions` | `CheckOutStaff`, `CheckInStaff`: the business rules, independent of the UI |
| `app/Filament/Resources` | Admin screens: each resource has its form (`Schemas/`), table (`Tables/`) and pages |
| `app/Filament/Actions/MovementActions.php` | The shared *Check out* / *Check in* buttons and forms |
| `app/Filament/Widgets` | Dashboard stats and "Currently out of office" table |
| `app/Livewire/Board.php` + `resources/views/livewire/board.blade.php` | The display board |
| `app/Policies` | Who may do what (admin vs clerk) |
| `database/migrations` | The schema, as code |
| `database/seeders` | `DatabaseSeeder` (units, reasons, first admin) and `DemoSeeder` (sample data) |
| `tests/Feature` | Workflow, permission and screen tests |

## Data model

```
units ─┐
       └─< staff ─┐
                  └─< movements >── movement_reasons
users ──(checked_out_by / checked_in_by)──┘
```

A staff member is **out** when they have a movement with no `in_at`.

## Development

```sh
php artisan test        # run the test suite
vendor/bin/pint         # format code
composer dev            # server, queue, logs and Vite together
```

Tests run on every push and pull request via GitHub Actions (`.github/workflows/tests.yml`).

## What changed from the old system

| 2016 version | Now |
|---|---|
| `mysql_*` functions (removed in PHP 7), SQL built from request input | Eloquent ORM with parameterised queries |
| Plain-text passwords | Hashed passwords |
| Admin pages reachable without logging in, deletes via GET links | Every page behind login, with role-based policies and CSRF-protected actions |
| Schema missing from the repo (`nrms.sql` was another project's) | Migrations in version control |
| "Today" matched by day-of-month only | Real date/time columns and queries |
| Five copy-pasted display pages | One live board, filterable by unit |
| Reports with no export | Filterable log with CSV export |
