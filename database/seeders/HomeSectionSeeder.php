<?php

namespace Database\Seeders;

use App\Models\HomeSection;
use Illuminate\Database\Seeder;

class HomeSectionSeeder extends Seeder
{
    public function run(): void
    {
        $sections = [
            [
                'section_key' => 'entrepreneurs',
                'title' => 'The Minds. The Vision. The Impact.',
                'big_text' => 'ENTREPRENEURS',
                'description' => 'Stories of entrepreneurs and leaders who are building India\'s future.',
                'image' => null,
                'link_text' => 'Explore All Profiles',
                'link_url' => '/category/culture',
                'position' => 1,
            ],
            [
                'section_key' => 'billionaires',
                'title' => 'Billionaires',
                'big_text' => 'BILLIONAIRES',
                'description' => 'The new generation of billionaires reshaping India\'s business landscape.',
                'image' => null,
                'link_text' => 'View all',
                'link_url' => '/category/business',
                'position' => 2,
            ],
            [
                'section_key' => 'stories_profiles',
                'title' => 'Stories & Profiles',
                'big_text' => 'PROFILES',
                'description' => 'In-depth profiles of leaders and changemakers.',
                'image' => null,
                'link_text' => 'View all',
                'link_url' => '/archive',
                'position' => 3,
            ],
            [
                'section_key' => 'reports',
                'title' => 'Reports',
                'big_text' => 'REPORTS',
                'description' => 'Comprehensive reports and analysis on key sectors.',
                'image' => null,
                'link_text' => 'View all',
                'link_url' => '/archive',
                'position' => 4,
            ],
        ];

        foreach ($sections as $section) {
            HomeSection::create(array_merge($section, ['status' => 1]));
        }
    }
}
