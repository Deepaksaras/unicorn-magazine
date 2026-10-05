<?php

namespace Database\Seeders;

use App\Models\Advertisement;
use App\Models\AdvertisementPlacement;
use Illuminate\Database\Seeder;

class AdvertisementSeeder extends Seeder
{
    public function run(): void
    {
        // Create placements
        $headerAd = AdvertisementPlacement::create([
            'name' => 'Header Ad',
            'slug' => 'header-ad',
            'description' => 'Horizontal ad below navigation',
            'dimensions' => '728x90',
            'location' => 'header',
            'max_ads' => 1,
            'is_active' => true,
            'status' => 1,
        ]);

        $sidebarAd = AdvertisementPlacement::create([
            'name' => 'Sidebar Ad',
            'slug' => 'sidebar-ad',
            'description' => 'Square ad in sidebar',
            'dimensions' => '300x250',
            'location' => 'sidebar',
            'max_ads' => 1,
            'is_active' => true,
            'status' => 1,
        ]);

        // Create advertisements
        Advertisement::create([
            'placement_id' => $headerAd->id,
            'title' => 'Your Advertisement Here',
            'slug' => 'header-ad-1',
            'description' => 'Premium advertising space for brands and businesses',
            'url' => '#',
            'alt_text' => 'Advertisement',
            'target' => '_blank',
            'is_active' => true,
            'status' => 1,
        ]);

        Advertisement::create([
            'placement_id' => $sidebarAd->id,
            'title' => 'Grow Your Business',
            'slug' => 'sidebar-ad-1',
            'description' => 'Reach the right audience with premium advertising opportunities.',
            'url' => '#',
            'alt_text' => 'Advertisement',
            'target' => '_blank',
            'is_active' => true,
            'status' => 1,
        ]);
    }
}
