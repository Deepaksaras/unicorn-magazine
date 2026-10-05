<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);
        Paginator::useBootstrapFive();

        $this->ensureStorageLink();

        Blade::component('admin-layout', \App\View\Components\AdminLayout::class);
        Blade::component('front-layout', \App\View\Components\FrontLayout::class);
    }

    /**
     * Uploaded images live in storage/app/public and are shown through the
     * public/storage link. Hosting uploads (FTP / File Manager) cannot copy
     * that link, so create it automatically when it is missing.
     */
    protected function ensureStorageLink(): void
    {
        $link = public_path('storage');

        if (!is_dir(storage_path('app/public'))) {
            return;
        }

        // A link left over from another folder (e.g. the site was moved from /test
        // to the main domain) points to a place that no longer exists → remove it.
        if (is_link($link) && !file_exists($link)) {
            @unlink($link);
        }

        if (file_exists($link) || is_link($link)) {
            return;
        }

        try {
            @symlink(storage_path('app/public'), $link);
        } catch (\Throwable $e) {
            // Not allowed on this server - run "php artisan storage:link" instead
        }
    }
}
