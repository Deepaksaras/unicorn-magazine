<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Additive changes needed so every public page can be driven from the CMS.
 * Safe to run on an existing database: it only adds columns / relaxes NOT NULL.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Posts: small label shown on cards (INTERVIEW, LEADERSHIP ...)
        Schema::table('posts', function (Blueprint $table) {
            if (!Schema::hasColumn('posts', 'badge')) {
                $table->string('badge', 50)->nullable()->after('format');
            }
        });

        // Advertisements: raw HTML / AdSense code
        Schema::table('advertisements', function (Blueprint $table) {
            if (!Schema::hasColumn('advertisements', 'code')) {
                $table->text('code')->nullable()->after('url');
            }
            if (!Schema::hasColumn('advertisements', 'button_text')) {
                $table->string('button_text', 50)->nullable()->after('code');
            }
        });

        // Advertising enquiries: industry field from the advertise form
        Schema::table('advertising_enquiries', function (Blueprint $table) {
            if (!Schema::hasColumn('advertising_enquiries', 'industry')) {
                $table->string('industry', 100)->nullable()->after('website');
            }
        });

        // Profiles: public profile cards (Stories & Profiles)
        Schema::table('profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('profiles', 'name')) {
                $table->string('name', 191)->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('profiles', 'slug')) {
                $table->string('slug', 191)->nullable()->after('name');
            }
            if (!Schema::hasColumn('profiles', 'label')) {
                $table->string('label', 100)->nullable()->after('profile_type');
            }
            if (!Schema::hasColumn('profiles', 'summary')) {
                $table->string('summary', 500)->nullable()->after('label');
            }
            if (!Schema::hasColumn('profiles', 'post_id')) {
                $table->unsignedBigInteger('post_id')->nullable()->after('summary');
            }
            if (!Schema::hasColumn('profiles', 'position')) {
                $table->integer('position')->default(0)->after('achievements');
            }
        });

        // Reports: data needed by the home page report list / exclusive card
        Schema::table('reports', function (Blueprint $table) {
            if (!Schema::hasColumn('reports', 'cover_image')) {
                $table->string('cover_image')->nullable()->after('description');
            }
            if (!Schema::hasColumn('reports', 'category_label')) {
                $table->string('category_label', 100)->nullable()->after('report_type');
            }
            if (!Schema::hasColumn('reports', 'author_name')) {
                $table->string('author_name', 191)->nullable()->after('category_label');
            }
            if (!Schema::hasColumn('reports', 'external_url')) {
                $table->string('external_url', 191)->nullable()->after('file_path');
            }
            if (!Schema::hasColumn('reports', 'is_exclusive')) {
                $table->boolean('is_exclusive')->default(false)->after('is_premium');
            }
        });

        // Pages: SEO fields edited alongside the page content
        Schema::table('pages', function (Blueprint $table) {
            if (!Schema::hasColumn('pages', 'meta_title')) {
                $table->string('meta_title', 191)->nullable()->after('featured_image');
            }
            if (!Schema::hasColumn('pages', 'meta_description')) {
                $table->string('meta_description', 300)->nullable()->after('meta_title');
            }
        });

        // Relax NOT NULL on foreign keys that should be optional
        $this->makeNullable('team_members', 'author_id');
        $this->makeNullable('profiles', 'user_id');
    }

    private function makeNullable(string $table, string $column): void
    {
        $driver = DB::getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'])) {
            DB::statement("ALTER TABLE `{$table}` MODIFY `{$column}` BIGINT UNSIGNED NULL");
        }
    }

    public function down(): void
    {
        Schema::table('posts', fn (Blueprint $t) => $t->dropColumn('badge'));
        Schema::table('pages', fn (Blueprint $t) => $t->dropColumn(['meta_title', 'meta_description']));
        Schema::table('advertisements', fn (Blueprint $t) => $t->dropColumn(['code', 'button_text']));
        Schema::table('advertising_enquiries', fn (Blueprint $t) => $t->dropColumn('industry'));
        Schema::table('profiles', fn (Blueprint $t) => $t->dropColumn(['name', 'slug', 'label', 'summary', 'post_id', 'position']));
        Schema::table('reports', fn (Blueprint $t) => $t->dropColumn(['cover_image', 'category_label', 'author_name', 'external_url', 'is_exclusive']));
    }
};
