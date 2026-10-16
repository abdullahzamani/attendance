# Staff Movement System

Laravel 13 + Filament 5 app that records staff leaving and returning to the office.
See `README.md` for setup and `docs/architecture.html` for the architecture overview.

## Conventions

- Business rules for check-out / check-in live in `app/Actions` (`CheckOutStaff`, `CheckInStaff`),
  not in Filament screens. Screens call them through `app/Filament/Actions/MovementActions.php`.
- Permissions live in `app/Policies`. Define every ability explicitly: Filament allows an action
  whose policy method is missing.
- A staff member is "out" when they have a movement with `in_at` null. Don't add a stored status flag.
- Run `php artisan test` and `vendor/bin/pint` before committing.

## Keep the architecture page current

`docs/architecture.html` is the living map of this system. When a change affects anything it shows
(models, migrations and fields, actions, policies and roles, routes and screens, polling intervals,
directory layout, test count, stack versions), update the matching figure, table or text in the
same commit. Keep its existing design: colour tokens in `:root` with dark-mode overrides, inline SVG
figures with labelled arrows, and the green / red / amber status colours.
