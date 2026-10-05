<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Detail pages for Profiles (/profile/{slug}) and Reports (/report/{slug}).
 *
 *  profiles.view_count – how often the profile page was opened
 *  reports.content     – full text shown on the report page (rich editor)
 *  reports.view_count  – how often the report page was opened
 *
 * Also points the "Stories & Profiles" links to the new /profiles page.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('profiles', 'view_count')) {
                $table->unsignedInteger('view_count')->default(0)->after('achievements');
            }
        });

        Schema::table('reports', function (Blueprint $table) {
            if (!Schema::hasColumn('reports', 'content')) {
                $table->longText('content')->nullable()->after('description');
            }
            if (!Schema::hasColumn('reports', 'view_count')) {
                $table->unsignedInteger('view_count')->default(0)->after('download_count');
            }
        });

        // Every profile / report needs a unique slug for its page address
        foreach (['profiles' => 'name', 'reports' => 'title'] as $table => $source) {
            $used = [];
            foreach (DB::table($table)->orderBy('id')->get(['id', 'slug', $source]) as $row) {
                $slug = $row->slug ?: Str::slug($row->{$source}) ?: $table . '-' . $row->id;
                $base = $slug;
                $i = 2;
                while (in_array($slug, $used, true)) {
                    $slug = $base . '-' . $i++;
                }
                $used[] = $slug;
                if ($slug !== $row->slug) {
                    DB::table($table)->where('id', $row->id)->update(['slug' => $slug]);
                }
            }
        }

        // "Stories & Profile" menu links + Home "View all" → the new profiles page
        DB::table('menu_items')->where('url', '/category/stories-profiles')->update(['url' => '/profiles']);

        $home = DB::table('pages')->where('slug', 'home')->value('id');
        if ($home) {
            $row = DB::table('page_sections')->where('page_id', $home)->where('section_name', 'profiles')->first();
            if ($row) {
                $settings = json_decode($row->settings ?? '{}', true) ?: [];
                if (($settings['link'] ?? '') === '/category/stories-profiles') {
                    $settings['link'] = '/profiles';
                    DB::table('page_sections')->where('id', $row->id)->update(['settings' => json_encode($settings)]);
                }
            }
        }
    }

    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropColumn(['content', 'view_count']);
        });
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn('view_count');
        });
    }
};
