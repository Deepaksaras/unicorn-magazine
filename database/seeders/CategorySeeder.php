<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Technology',
                'slug' => 'technology',
                'description' => 'Latest technology news, reviews, and insights',
                'position' => 1,
                'status' => 1,
            ],
            [
                'name' => 'Culture',
                'slug' => 'culture',
                'description' => 'Arts, entertainment, and cultural trends',
                'position' => 2,
                'status' => 1,
            ],
            [
                'name' => 'Business',
                'slug' => 'business',
                'description' => 'Business news, market analysis, and entrepreneurship',
                'position' => 3,
                'status' => 1,
            ],
            [
                'name' => 'Science',
                'slug' => 'science',
                'description' => 'Scientific discoveries, research, and innovations',
                'position' => 4,
                'status' => 1,
            ],
            [
                'name' => 'Lifestyle',
                'slug' => 'lifestyle',
                'description' => 'Health, wellness, travel, and lifestyle content',
                'position' => 5,
                'status' => 1,
            ],
            // Sub-categories
            [
                'name' => 'Artificial Intelligence',
                'slug' => 'artificial-intelligence',
                'description' => 'AI news, machine learning, and deep learning',
                'parent_id' => 1,
                'position' => 1,
                'status' => 1,
            ],
            [
                'name' => 'Startups',
                'slug' => 'startups',
                'description' => 'Startup news, funding, and entrepreneurship',
                'parent_id' => 3,
                'position' => 1,
                'status' => 1,
            ],
            [
                'name' => 'Space',
                'slug' => 'space',
                'description' => 'Space exploration, astronomy, and astrophysics',
                'parent_id' => 4,
                'position' => 1,
                'status' => 1,
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
