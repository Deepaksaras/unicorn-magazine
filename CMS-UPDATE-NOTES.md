# The Unicorn Magazine — CMS update

## Run these once (in `C:\xampp\htdocs\parth`)

```bash
php artisan migrate                              # 3 new migrations — only ADD tables/columns, no data loss
php artisan db:seed --class=CmsContentSeeder     # fills menus, pages, team, jobs, ads… from the static design (safe to re-run)
php artisan storage:link                         # makes uploaded images visible (public/storage)
php artisan optimize:clear
```

Log in at **/admin** (seeded admin: `admin@unicornmagazine.com` / `password` — change it in Users → My account).

## What is managed where

| Website part | Admin screen |
|---|---|
| Top “IMPORTANT” bar | Website → Breaking News |
| Header menu, footer columns, footer legal links, ☰ side panel | Website → Menus |
| Every text/image block on Home, About, Contact, Career, Advertise, Privacy, Terms | Website → Pages |
| “Meet our team” (About) | Website → Team Members |
| “Current openings” (Career) | Website → Job Openings |
| Stories on Home / category / article pages | Content → Articles (Featured = home hero + sidebars) |
| Stories & Profiles block | Content → Profiles |
| Reports block, /reports, exclusive card | Content → Reports |
| Ad slots (image, AdSense code or text) | Advertising → Advertisements |
| Contact form / Advertise form / newsletter | Inbox → Contact Messages / Ad Enquiries / Subscribers |
| Logo, favicon, footer texts, social links, SEO, AdSense script | System → Settings |

## New public URLs
`/latest`, `/category/{slug}`, `/tag/{slug}`, `/search?q=`, `/archive`, `/reports`, `/about`, `/contact`,
`/career`, `/advertise-with-us`, `/privacy-policy`, `/terms-conditions`. Old `/careers`, `/advertise`,
`/terms-of-service` redirect.

## Files you can delete (no longer used)
- `app/Http/Controllers/Admin/AuthorController.php`, `app/Http/Controllers/Admin/CommentController.php`
- `resources/views/admin/authors/`, `resources/views/admin/comments/`, `resources/views/admin/pages/create.blade.php`

`backup_before_cms_update.zip` contains the previous version of every file that was overwritten.
