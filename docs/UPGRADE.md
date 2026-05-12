# Upgrading an existing installation

ServiceFlow applies schema changes through Laravel migrations. **Pulling new code does not change the database by itself.** Every existing environment must run pending migrations after deploying new commits.

This keeps upgrades safe and **backward compatible at the database layer**: migrations are written to apply cleanly on top of older schemas (additive changes, defaults, nullable columns) so that `git pull` followed by `php artisan migrate --force` brings the database up to date without requiring a fresh install.

---

## Standard upgrade procedure

Run these on the server (maintenance window optional):

1. Put the app in maintenance mode if needed: `php artisan down`
2. Pull or deploy the new application code.
3. Install PHP dependencies if they changed: `composer install --no-dev --optimize-autoloader`
4. Install/build frontend assets if your process requires it: `npm ci && npm run build`
5. **Apply database changes:** `php artisan migrate --force`  
   (`--force` is required when `APP_ENV=production`.)
6. Refresh caches as you normally do, for example:  
   `php artisan config:cache` and `php artisan route:cache` (only if you use config/route caching).
7. Bring the app back: `php artisan up`

Automate step 5 in your deployment pipeline so it cannot be skipped.

---

## Check for pending migrations

Before or after deploy, verify whether the database matches the code:

```bash
php artisan serviceflow:upgrade-check
```

To fail a script or CI job when migrations are still pending (for example before switching traffic to new nodes):

```bash
php artisan serviceflow:upgrade-check --fail-on-pending
```

You can also use Laravel’s built-in status:

```bash
php artisan migrate:status
```

---

## First install vs upgrades

- **First install** uses the web installer, which runs `migrate` and `db:seed` once.
- **Upgrades** only need `migrate` (and optional `db:seed` only if release notes say so—never assume seeding on production without review).

---

## Backward-compatible migrations (contributor rules)

Follow these so existing installations stay healthy when they pull and migrate:

1. **Prefer additive changes**  
   Add new tables and columns with sensible defaults or nullable columns. Avoid requiring immediate data backfills that migrations cannot perform safely.

2. **Avoid destructive steps in the same release as code that removes usage**  
   Prefer: ship code that tolerates old and new schema → migrate → later release drops obsolete columns/tables.

3. **Default values for NOT NULL columns**  
   When adding a required column to a table that already has rows, supply a `default(...)` or make the column nullable until backfill completes.

4. **Rename in two steps when needed**  
   Add new column → copy data → deploy code reading new column → later migration drops old column.

5. **Order and idempotency**  
   Migrations must run successfully on an empty database (fresh install) and on production databases that may be several releases behind.

6. **Heavy data moves**  
   Use chunked updates or dedicated artisan commands documented in release notes; keep migrations fast enough not to lock tables unreasonably.

---

## Release checklist for maintainers

- [ ] All schema changes are in `database/migrations/`.
- [ ] Fresh `php artisan migrate` works from zero.
- [ ] Upgrade from the previous release’s schema works with only `migrate`.
- [ ] Release notes mention `php artisan migrate --force` for operators.

---

## Related files

- Installer (initial migrate + seed): `app/Services/Installer/DatabaseInstaller.php`
- Pending migration check: `php artisan serviceflow:upgrade-check`
