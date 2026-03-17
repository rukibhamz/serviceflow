# cPanel Email Pipe Setup

This guide explains how to configure cPanel to pipe incoming emails to ServiceFlow so they are automatically converted into tickets.

## Prerequisites

- cPanel hosting account with shell access or `.forward` file support
- PHP available at `/usr/bin/php` or `/usr/local/bin/php`
- ServiceFlow installed and configured at a known path (e.g. `/home/username/public_html`)

## Step 1 — Verify the pipe script

Ensure `scripts/pipe.php` exists in your ServiceFlow installation root and is executable:

```bash
chmod +x /home/username/public_html/scripts/pipe.php
```

## Step 2 — Create the `.forward` file

In the home directory of the email account you want to pipe (e.g. `support@yourdomain.com`), create or edit the `.forward` file:

```
\support@yourdomain.com
"|/usr/bin/php /home/username/public_html/scripts/pipe.php"
```

> The leading backslash before the address (`\support@yourdomain.com`) ensures a copy of the email is still delivered to the mailbox. Remove it if you only want piping without local delivery.

## Step 3 — Set up via cPanel Email Forwarders (alternative)

1. Log in to cPanel.
2. Go to **Email** → **Forwarders**.
3. Click **Add Forwarder** for the desired address.
4. Choose **Pipe to a Program** and enter:
   ```
   /home/username/public_html/scripts/pipe.php
   ```
5. Save the forwarder.

## Step 4 — Test the pipe

Send a test email to the configured address, then check:

```bash
php artisan tinker
>>> App\Models\Ticket::latest()->first()
```

Or check the application log:

```bash
tail -f storage/logs/laravel.log
```

## Troubleshooting

- **Permission denied**: Ensure `scripts/pipe.php` is executable (`chmod +x`).
- **PHP not found**: Update the shebang line in `scripts/pipe.php` to match your server's PHP path (`which php`).
- **Empty tickets**: Check that the email is being delivered to the pipe and not filtered as spam.
- **Errors in log**: The command catches all exceptions and exits 0 to prevent email bouncing. Check `storage/logs/laravel.log` for details.
