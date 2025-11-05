# Repository Guidelines

## Project Structure & Module Organization
- Backend source lives under `app/`, with domain controllers grouped by module in `app/Http/Controllers/Multban` and Eloquent models in `app/Models/Multban`.
- Routes are split into feature-specific registrars inside `app/Http/Routes`, all pulled into `routes/web.php`.
- Views reside in `resources/views`, using `layouts/app-master.blade.php` plus module folders such as `resources/views/Multban/cliente`.
- Database assets are in `database/migrations`, `database/seeders`, and `infra/mysql/init` for Dockerized MySQL provisioning.

## Build, Test, and Development Commands
- `composer install` and `npm install` pull PHP and Node dependencies.
- `composer run dev` starts the Laravel server, queue listener, and Vite in parallel for local development.
- `npm run build` compiles production assets via Vite/Tailwind.
- `php artisan migrate --seed` prepares schemas and seeds the admin user plus permission tree.
- `php artisan queue:work` runs background job workers; keep it active when testing async flows.

## Coding Style & Naming Conventions
- Follow Laravel 12 defaults: PSR-12 PHP formatting with 4-space indentation.
- Run `composer format` (Laravel Pint) before opening a PR; use `composer lint` to check without writing changes.
- Blade templates should prefer kebab-cased file names (e.g., `Multban/cliente/index.blade.php`) and section names matching route intents.
- Keep controller method names RESTful (`index`, `store`, `update`, etc.) and align route names (`cliente.store`, `produto.index`) with Spatie permission strings.

## Testing Guidelines
- Primary testing uses PHPUnit (`php artisan test`); add Livewire/browser interaction coverage when modifying UI logic.
- Place feature tests inside `tests/Feature`, mirroring module paths (e.g., `Multban/Cliente`).
- Name tests using descriptive phrases, `test_*` for PHPUnit or annotated methods.
- Aim to cover new database queries, authorization checks, and tenancy edge cases introduced by your change.

## Commit & Pull Request Guidelines
- Craft commits in imperative mood (`Add tenant guard to cliente routes`) and keep related changes together; avoid large multi-feature commits.
- Pull requests should include: purpose summary, affected modules, testing notes (`php artisan test`, manual steps), and screenshots for UI tweaks.
- Link relevant issues or tickets and call out migrations, seeds, or environment changes so reviewers can prepare.

## Security & Configuration Tips
- Always load env variables via `.env`; never hardcode credentials. Use `php artisan key:generate` for new environments.
- Verify tenant isolation by testing with distinct `emp_id` values; controllers depend on `TenantManager` and related middleware for access control.
