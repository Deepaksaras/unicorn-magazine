<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        $tags = [
            ['name' => 'AI', 'slug' => 'ai', 'description' => 'Artificial Intelligence'],
            ['name' => 'Machine Learning', 'slug' => 'machine-learning', 'description' => 'Machine Learning topics'],
            ['name' => 'Blockchain', 'slug' => 'blockchain', 'description' => 'Blockchain and crypto'],
            ['name' => 'Startup', 'slug' => 'startup', 'description' => 'Startup ecosystem'],
            ['name' => 'Funding', 'slug' => 'funding', 'description' => 'Funding and investment'],
            ['name' => 'Innovation', 'slug' => 'innovation', 'description' => 'Innovation and disruption'],
            ['name' => 'Climate', 'slug' => 'climate', 'description' => 'Climate change and sustainability'],
            ['name' => 'Health', 'slug' => 'health', 'description' => 'Health and wellness'],
            ['name' => 'Travel', 'slug' => 'travel', 'description' => 'Travel and tourism'],
            ['name' => 'Food', 'slug' => 'food', 'description' => 'Food and cuisine'],
            ['name' => 'Fashion', 'slug' => 'fashion', 'description' => 'Fashion and style'],
            ['name' => 'Sports', 'slug' => 'sports', 'description' => 'Sports news and updates'],
            ['name' => 'Entertainment', 'slug' => 'entertainment', 'description' => 'Entertainment industry'],
            ['name' => 'Gadgets', 'slug' => 'gadgets', 'description' => 'Gadgets and devices'],
            ['name' => 'Software', 'slug' => 'software', 'description' => 'Software and apps'],
        ];

        foreach ($tags as $tag) {
            Tag::create([
                'name' => $tag['name'],
                'slug' => $tag['slug'],
                'description' => $tag['description'],
                'status' => 1,
            ]);
        }
    }
}
