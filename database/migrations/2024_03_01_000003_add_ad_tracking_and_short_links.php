<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 *  ad_clicks            – one row per advertisement click (time, page, device)
 *                         Daily totals go to the existing advertising_stats table.
 *  posts/profiles/reports.short_code   – code for the short link /s/{code}
 *  posts/profiles/reports.short_clicks – how often that short link was opened
 *  advertisement_placements "popup-ad" – slot for the pop-up advertisement
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('ad_clicks')) {
            Schema::create('ad_clicks', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('advertisement_id');
                $table->string('visitor_id', 40)->nullable();
                $table->string('path', 191)->nullable();       // page the ad was clicked on
                $table->string('device', 10)->default('desktop');
                $table->timestamp('created_at')->useCurrent();

                $table->index(['advertisement_id', 'created_at']);
                $table->index('created_at');
            });
        }

        foreach (['posts', 'profiles', 'reports'] as $name) {
            Schema::table($name, function (Blueprint $table) use ($name) {
                if (!Schema::hasColumn($name, 'short_code')) {
                    $table->string('short_code', 12)->nullable()->unique();
                }
                if (!Schema::hasColumn($name, 'short_clicks')) {
                    $table->unsignedInteger('short_clicks')->default(0);
                }
            });
        }
        // Codes for existing items are created automatically the first time they are shown.

        if (!DB::table('advertisement_placements')->where('slug', 'popup-ad')->exists()) {
            DB::table('advertisement_placements')->insert([
                'name' => 'Pop-up Ad', 'slug' => 'popup-ad',
                'description' => 'Pop-up window (settings: Advertising → Pop-up Ad)',
                'dimensions' => '600x500', 'location' => 'popup', 'max_ads' => 1,
                'is_active' => 1, 'status' => 1, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ad_clicks');
        foreach (['posts', 'profiles', 'reports'] as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->dropUnique([ 'short_code' ]);
                $table->dropColumn(['short_code', 'short_clicks']);
            });
        }
    }
};
