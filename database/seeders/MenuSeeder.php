<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        // Header menu
        $headerMenu = Menu::create([
            'name' => 'Header Menu',
            'slug' => 'header-menu',
            'location' => 'header',
            'description' => 'Main navigation menu in the header',
            'status' => 1,
        ]);

        $headerItems = [
            ['title' => 'Home', 'url' => '/', 'position' => 1],
            ['title' => 'Technology', 'url' => '/category/technology', 'position' => 2],
            ['title' => 'Culture', 'url' => '/category/culture', 'position' => 3],
            ['title' => 'Business', 'url' => '/category/business', 'position' => 4],
            ['title' => 'Science', 'url' => '/category/science', 'position' => 5],
            ['title' => 'Lifestyle', 'url' => '/category/lifestyle', 'position' => 6],
            ['title' => 'Archive', 'url' => '/archive', 'position' => 7],
        ];

        foreach ($headerItems as $item) {
            MenuItem::create([
                'menu_id' => $headerMenu->id,
                'title' => $item['title'],
                'url' => $item['url'],
                'position' => $item['position'],
                'status' => 1,
            ]);
        }

        // Footer menu
        $footerMenu = Menu::create([
            'name' => 'Footer Menu',
            'slug' => 'footer-menu',
            'location' => 'footer',
            'description' => 'Footer navigation menu',
            'status' => 1,
        ]);

        $footerItems = [
            ['title' => 'About Us', 'url' => '/about', 'position' => 1],
            ['title' => 'Contact', 'url' => '/contact', 'position' => 2],
            ['title' => 'Privacy Policy', 'url' => '/privacy-policy', 'position' => 3],
            ['title' => 'Terms of Service', 'url' => '/terms-of-service', 'position' => 4],
            ['title' => 'Advertise', 'url' => '/advertise', 'position' => 5],
        ];

        foreach ($footerItems as $item) {
            MenuItem::create([
                'menu_id' => $footerMenu->id,
                'title' => $item['title'],
                'url' => $item['url'],
                'position' => $item['position'],
                'status' => 1,
            ]);
        }
    }
}
