<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            UserSeeder::class,
            CategorySeeder::class,
            TagSeeder::class,
            PostSeeder::class,
            SettingsSeeder::class,
            MenuSeeder::class,
            BreakingNewsSeeder::class,
            HomeSectionSeeder::class,
            AdvertisementSeeder::class,
            CmsContentSeeder::class,
        ]);
    }
}
