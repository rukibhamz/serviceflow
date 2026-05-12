# Running database migrations on cPanel (without SSH)

Many shared cPanel accounts only offer **FTP / File Manager** and no shell. ServiceFlow still needs **`php artisan migrate --force`** after you upload new code so new tables and columns exist.

Pick **one** method below.

---

## Method 1 — cPanel Cron Jobs (often works without SSH)

cPanel can run PHP on a schedule. Use it **once** to migrate, then delete the cron job.

1. In cPanel open **Cron Jobs** (Advanced section).
2. Under **Add New Cron Job**, choose **Once** or **Common Settings → Once Per Minute** temporarily (you will remove this right after).
3. **Command** — adjust paths to match your account (ask your host if unsure):

   ```bash
   cd /home/yourcpanelusername/public_html/serviceflow && /usr/local/bin/php artisan migrate --force
   ```

   Common PHP paths on cPanel:

   - `/usr/local/bin/php`
   - `/usr/bin/php`
   - `/opt/cpanel/ea-php82/root/usr/bin/php` (replace `82` with your PHP version from **MultiPHP Manager**)

   The application root is the folder that contains `artisan` (not necessarily `public_html` if you installed in a subdirectory).

4. Save the cron job, wait **one minute**, then confirm migrations ran (admin dashboard loads without missing-table errors).
5. **Delete** the cron job so migrations do not run every minute.

---

## Method 2 — Browser / curl “web migrate” (optional)

If your deployment includes the optional token in `.env`, you can trigger migrations with an HTTPS POST from any machine (no SSH).

1. On the server, edit `.env` and set a **long random secret** (only you know it):

   ```env
   MIGRATE_WEB_TOKEN=your-long-random-secret-here
   ```

2. Clear config cache if you use it (otherwise Laravel may ignore the new token). Without SSH, use **Cron** once:

   ```bash
   cd /home/yourcpanelusername/path/to/serviceflow && /usr/local/bin/php artisan config:clear
   ```

   Or combine with migrations in one cron line:

   ```bash
   cd /home/yourcpanelusername/path/to/serviceflow && /usr/local/bin/php artisan config:clear && /usr/local/bin/php artisan migrate --force
   ```

   After that, delete the cron job if you only needed it once.

3. Send **HTTPS POST** (replace URL and token):

   ```bash
   curl -X POST "https://yourdomain.com/_serviceflow/migrate" -H "X-Migrate-Token: your-long-random-secret-here"
   ```

   Or use a REST client with:

   - URL: `https://yourdomain.com/_serviceflow/migrate`
   - Method: POST
   - Header: `X-Migrate-Token: your-long-random-secret-here`

4. Read the JSON response: `ok: true` and migration output means success.

5. **Remove** `MIGRATE_WEB_TOKEN` from `.env` (or empty it) so the endpoint is disabled again. Reduces risk if someone ever saw your `.env`.

**Security**

- Use HTTPS only.
- Treat the token like a password; never commit it to git.
- Disable the endpoint after migrations finish.

If `MIGRATE_WEB_TOKEN` is empty, the route is **not** registered.

---

## Method 3 — Run migrations from your own PC

If the host allows **remote MySQL** (cPanel → **Remote MySQL** → add your IP):

1. Copy `.env` from the server (or create one) with **the same** `DB_*` credentials as production and `APP_ENV=production`.
2. On your computer, from the project folder:

   ```bash
   php artisan migrate --force
   ```

This runs migrations **against the live database** from your machine. Revoke your IP in Remote MySQL when finished.

---

## Method 4 — cPanel “Terminal” or SSH

If your plan includes **Terminal** (cPanel → Advanced → Terminal) or SSH:

```bash
cd /home/username/path/to/serviceflow
php artisan migrate --force
```

---

## Subdirectories and HTTPS

- If the site lives under a path (for example `https://domain.com/serviceflow/public/`), your POST URL and cron `cd` path must include that folder—wherever `artisan` lives.
- Always prefer **HTTPS** for the web migrate method.

---

## See also

- [UPGRADE.md](./UPGRADE.md) — general upgrade order and backward-compatible migrations.
