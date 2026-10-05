<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Website visitor tracking (Admin → Dashboard → Website visitors).
 *
 *  site_visits      – one row per page view on the public website
 *  visitor_presence – one row per visitor, "last seen" time → "Online now"
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('site_visits')) {
            Schema::create('site_visits', function (Blueprint $table) {
                $table->id();
                $table->string('visitor_id', 40);                 // anonymous id from a cookie (no personal data)
                $table->string('path', 191);                      // page address, e.g. /article/my-story
                $table->string('referrer_host', 191)->nullable(); // site the visitor came from (google.com …)
                $table->string('device', 10)->default('desktop'); // desktop | mobile | tablet
                $table->timestamp('created_at')->useCurrent();

                $table->index('created_at');
                $table->index(['created_at', 'visitor_id']);
            });
        }

        if (!Schema::hasTable('visitor_presence')) {
            Schema::create('visitor_presence', function (Blueprint $table) {
                $table->string('visitor_id', 40)->primary();
                $table->string('path', 191)->nullable();
                $table->timestamp('last_seen_at')->useCurrent();

                $table->index('last_seen_at');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('visitor_presence');
        Schema::dropIfExists('site_visits');
    }
};
