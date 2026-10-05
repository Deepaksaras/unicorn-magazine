<?php

use App\Http\Controllers\AdClickController;
use App\Http\Controllers\AdViewController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProfilesController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ShortLinkController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\SubscribeOAuthController;
use App\Http\Controllers\TrackController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public website routes
|--------------------------------------------------------------------------
| Every page below gets its content from the admin CMS (/admin).
| Admin panel routes are in routes/admin.php (loaded at the bottom).
*/

/* ---------- Home + newsletter ---------- */
Route::get('/', [HomeController::class, 'index'])->name('home');                          // Home page
Route::post('/subscribe', [HomeController::class, 'subscribe'])                           // Newsletter form (footer + popup)
    ->middleware('throttle:10,1')->name('subscribe');

/* ---------- Subscribe with Google / LinkedIn (keys: Admin → Website → Subscribe Pop-up) ---------- */
Route::get('/subscribe/{provider}', [SubscribeOAuthController::class, 'redirect'])
    ->where('provider', 'google|linkedin')->middleware('throttle:20,1')->name('subscribe.oauth');
Route::get('/subscribe/{provider}/callback', [SubscribeOAuthController::class, 'callback'])
    ->where('provider', 'google|linkedin')->name('subscribe.oauth.callback');

/* ---------- Stories (Admin → Content → Articles / Categories / Tags) ---------- */
Route::get('/latest', [CategoryController::class, 'latest'])->name('latest');               // All latest news
Route::get('/category/{slug}', [CategoryController::class, 'show'])->name('category');      // One category
Route::get('/tag/{slug}', [CategoryController::class, 'tag'])->name('tag');                 // One tag
Route::get('/archive', [CategoryController::class, 'archive'])->name('archive');            // Archive by month
Route::get('/search', [CategoryController::class, 'search'])->name('search');               // Search results (?q=)
Route::get('/article/{slug}', [ArticleController::class, 'show'])->name('article');         // Single article

/* ---------- Stories & Profiles (Admin → Content → Profiles) ---------- */
Route::get('/profiles', [ProfilesController::class, 'index'])->name('profiles.index');         // all profiles (?type=founder)
Route::get('/profile/{slug}', [ProfilesController::class, 'show'])->name('profile');           // one profile

/* ---------- Reports (Admin → Content → Reports) ---------- */
Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');              // all reports (?type=market)
Route::get('/report/{slug}', [ReportController::class, 'show'])->name('report');                // one report
Route::get('/reports/{slug}/download', [ReportController::class, 'download'])->name('reports.download');

/* ---------- Company pages (Admin → Website → Pages) ---------- */
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::post('/contact', [PageController::class, 'sendContact'])                          // Contact form → Inbox → Contact Messages
    ->middleware('throttle:6,1')->name('contact.send');
Route::get('/career', [PageController::class, 'career'])->name('career');
Route::get('/advertise-with-us', [PageController::class, 'advertise'])->name('advertise');
Route::post('/advertise-with-us', [PageController::class, 'sendEnquiry'])                // Advertise form → Inbox → Ad Enquiries
    ->middleware('throttle:6,1')->name('advertise.send');
Route::get('/privacy-policy', [PageController::class, 'privacy'])->name('privacy');
Route::get('/terms-conditions', [PageController::class, 'terms'])->name('terms');

// 404 page preview (the "View page" button in Admin → Pages → Page Not Found)
Route::get('/page-not-found', fn () => response()->view('errors.404', [], 404))->name('not-found');

/* ---------- Sitemap for Google (built automatically; listed in public/robots.txt) ---------- */
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');

/* ---------- Short links for sharing: /s/K7x2Qa → article / profile / report ---------- */
Route::get('/s/{code}', ShortLinkController::class)->where('code', '[A-Za-z0-9]{4,12}')->name('short');

/* ---------- Old links still saved in the menus → redirect to the new pages ---------- */
Route::redirect('/advertise', '/advertise-with-us', 301);
Route::redirect('/terms-of-service', '/terms-conditions', 301);

/* ---------- Advertisement click counter (Admin → Advertising) ---------- */
Route::get('/ad/{id}/click', AdClickController::class)->whereNumber('id')->name('ad.click');
Route::post('/ad/{id}/view', AdViewController::class)->whereNumber('id')->middleware('throttle:60,1')->name('ad.view'); // pop-up opened

/* ---------- Visitor counter (Admin → Dashboard → Website visitors) ---------- */
Route::post('/t/hit', [TrackController::class, 'hit'])->middleware('throttle:240,1')->name('track.hit');    // page opened
Route::post('/t/ping', [TrackController::class, 'ping'])->middleware('throttle:240,1')->name('track.ping'); // still on the page

/*
|--------------------------------------------------------------------------
| Login system
|--------------------------------------------------------------------------
| routes/auth.php = login + "Forgot password" email links (used by the
| "Forgot?" link on the admin login screen).
| After signing in there, Laravel opens /dashboard, which goes to the admin.
*/
Route::get('/dashboard', fn () => redirect()->route('admin.dashboard'))
    ->middleware('auth')->name('dashboard');

require __DIR__ . '/auth.php';

/* ---------- Admin CMS (/admin) ---------- */
require __DIR__ . '/admin.php';
