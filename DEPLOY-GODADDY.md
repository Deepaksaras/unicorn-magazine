# Upload The Unicorn Magazine to GoDaddy (/test)

This folder is complete and ready for https://theunicornmagazine.com/test
(`.env` has the live database; the root `.htaccess` is the GoDaddy version).

## 1. Make the zip (on your PC)

Open `C:\xampp\htdocs\parth`, select everything inside, right-click → Compress to ZIP —
but first leave these OUT of the selection:

- `node_modules` (not needed on the server)
- `storage` (the server already has its own: uploaded images, logs, sessions)
- `public\storage` (a Windows shortcut; the server makes its own link automatically)

Everything else goes in, including `vendor`, `.env` and `.htaccess`.

## 2. Upload

cPanel → File Manager → `public_html/test` → Upload the zip → right-click it → Extract → allow overwrite.
Delete the zip afterwards.

## 3. Database (phpMyAdmin → unicorn_magazine → SQL tab → paste → Go)

- `database_updates.sql` – visitor counter + profile/report pages (skip if you already ran it)
- `database_updates_2.sql` – ad click tracking + short links + pop-up ad slot

Or with SSH/Terminal instead of both: `cd public_html/test && php artisan migrate --force`

## 4. Clear caches (File Manager)

Delete everything inside (keep `.gitignore`):
- `test/bootstrap/cache/`
- `test/storage/framework/views/`

## 5. Check

- Home page loads with styles; a wrong address shows the 404 page
- An article → Copy link → paste in a private window → opens the article
- /test/admin → Dashboard: Online now · Visitors · Page views · Ad clicks
- Advertising → Advertisements → Report button on an ad
- Advertising → Pop-up Ad → New pop-up ad → switch ON → Preview on website

## Note about XAMPP

`.env` points to the live GoDaddy database, so the local XAMPP copy will not connect.
To work locally, use your XAMPP `.env` backup (DB_HOST=127.0.0.1).
