<?php

use App\Http\Controllers\Admin\AdvertisementController;
use App\Http\Controllers\Admin\AdvertisementPlacementController;
use App\Http\Controllers\Admin\AdvertisingEnquiryController;
use App\Http\Controllers\Admin\ArticleController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\BreakingNewsController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\JobOpeningController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\PopupController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SeoController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\SubscribePopupController;
use App\Http\Controllers\Admin\SubscriberController;
use App\Http\Controllers\Admin\TagController;
use App\Http\Controllers\Admin\TeamMemberController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin CMS  (/admin)
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->group(function () {

    // Sign in / out
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::middleware(['auth', 'admin'])->group(function () {

        Route::get('/', fn () => redirect()->route('admin.dashboard'));
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/live', [DashboardController::class, 'live'])->name('dashboard.live'); // "Online now" auto-refresh

        // Content
        Route::resource('articles', ArticleController::class);
        Route::resource('categories', CategoryController::class);
        Route::resource('tags', TagController::class);
        Route::resource('profiles', ProfileController::class);
        Route::resource('reports', ReportController::class);

        Route::get('media', [MediaController::class, 'index'])->name('media.index');
        Route::post('media/upload', [MediaController::class, 'upload'])->name('media.upload');
        Route::put('media/{medium}', [MediaController::class, 'update'])->name('media.update');
        Route::delete('media/{medium}', [MediaController::class, 'destroy'])->name('media.destroy');

        // Website
        Route::get('pages', [PageController::class, 'index'])->name('pages.index');
        Route::get('pages/{key}/edit', [PageController::class, 'edit'])->name('pages.edit');
        Route::put('pages/{key}', [PageController::class, 'update'])->name('pages.update');

        Route::post('breaking-news/reorder', [BreakingNewsController::class, 'reorder'])->name('breaking-news.reorder');
        Route::resource('breaking-news', BreakingNewsController::class)->except('show')->parameters(['breaking-news' => 'breakingNews']);

        Route::post('team-members/reorder', [TeamMemberController::class, 'reorder'])->name('team-members.reorder');
        Route::resource('team-members', TeamMemberController::class)->except('show');

        Route::post('job-openings/reorder', [JobOpeningController::class, 'reorder'])->name('job-openings.reorder');
        Route::resource('job-openings', JobOpeningController::class)->except('show');

        Route::resource('menus', MenuController::class);
        Route::post('menus/{menu}/items', [MenuController::class, 'storeItem'])->name('menus.items.store');
        Route::post('menus/{menu}/items/reorder', [MenuController::class, 'reorder'])->name('menus.items.reorder');
        Route::put('menus/{menu}/items/{item}', [MenuController::class, 'updateItem'])->name('menus.items.update');
        Route::delete('menus/{menu}/items/{item}', [MenuController::class, 'destroyItem'])->name('menus.items.destroy');

        // Advertising
        Route::get('advertisements/{advertisement}/report', [AdvertisementController::class, 'report'])->name('advertisements.report');        // views & clicks report
        Route::get('advertisements/{advertisement}/report/export', [AdvertisementController::class, 'export'])->name('advertisements.export'); // CSV
        Route::resource('advertisements', AdvertisementController::class);
        Route::resource('advertisement-placements', AdvertisementPlacementController::class);
        Route::get('subscribe-popup', [SubscribePopupController::class, 'edit'])->name('subscribe-popup.edit');     // Subscribe pop-up settings
        Route::put('subscribe-popup', [SubscribePopupController::class, 'update'])->name('subscribe-popup.update');
        Route::get('popup', [PopupController::class, 'edit'])->name('popup.edit');       // Pop-up Ad settings
        Route::put('popup', [PopupController::class, 'update'])->name('popup.update');

        // Inbox
        Route::get('contact-messages', [ContactMessageController::class, 'index'])->name('contact-messages.index');
        Route::get('contact-messages/{contactMessage}', [ContactMessageController::class, 'show'])->name('contact-messages.show');
        Route::patch('contact-messages/{contactMessage}/toggle', [ContactMessageController::class, 'toggleRead'])->name('contact-messages.toggle');
        Route::delete('contact-messages/{contactMessage}', [ContactMessageController::class, 'destroy'])->name('contact-messages.destroy');

        Route::get('advertising-enquiries', [AdvertisingEnquiryController::class, 'index'])->name('advertising-enquiries.index');
        Route::get('advertising-enquiries/{advertisingEnquiry}', [AdvertisingEnquiryController::class, 'show'])->name('advertising-enquiries.show');
        Route::put('advertising-enquiries/{advertisingEnquiry}', [AdvertisingEnquiryController::class, 'update'])->name('advertising-enquiries.update');
        Route::delete('advertising-enquiries/{advertisingEnquiry}', [AdvertisingEnquiryController::class, 'destroy'])->name('advertising-enquiries.destroy');

        Route::get('subscribers', [SubscriberController::class, 'index'])->name('subscribers.index');
        Route::get('subscribers/export', [SubscriberController::class, 'export'])->name('subscribers.export');
        Route::patch('subscribers/{subscriber}/toggle', [SubscriberController::class, 'toggle'])->name('subscribers.toggle');
        Route::delete('subscribers/{subscriber}', [SubscriberController::class, 'destroy'])->name('subscribers.destroy');

        // System
        Route::resource('users', UserController::class);
        Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');
        Route::get('seo', [SeoController::class, 'edit'])->name('seo.edit');                // SEO defaults + listing pages
        Route::put('seo', [SeoController::class, 'update'])->name('seo.update');
    });
});
