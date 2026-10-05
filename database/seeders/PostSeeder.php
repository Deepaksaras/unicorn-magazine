<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@unicornmagazine.com')->first();
        $editor = User::where('email', 'editor@unicornmagazine.com')->first();
        $author = User::where('email', 'author@unicornmagazine.com')->first();

        $techCategory = Category::where('slug', 'technology')->first();
        $cultureCategory = Category::where('slug', 'culture')->first();
        $businessCategory = Category::where('slug', 'business')->first();
        $scienceCategory = Category::where('slug', 'science')->first();
        $lifestyleCategory = Category::where('slug', 'lifestyle')->first();

        $posts = [
            [
                'user_id' => $admin->id,
                'category_id' => $techCategory->id,
                'title' => 'The Future of AI: What\'s Next for Humanity',
                'slug' => 'the-future-of-ai-whats-next-for-humanity',
                'excerpt' => 'Exploring the latest breakthroughs in artificial intelligence and what they mean for our future.',
                'content' => '<p>Artificial intelligence has rapidly evolved from a niche research topic to one of the most transformative technologies of our era. As we stand at the precipice of a new technological revolution, it\'s worth examining what the future holds for AI and humanity.</p><p>The implications of advanced AI systems are far-reaching, affecting everything from healthcare and education to transportation and entertainment. As we continue to push the boundaries of what\'s possible, we must also consider the ethical implications and ensure that these powerful tools are developed responsibly.</p>',
                'type' => 'article',
                'format' => 'standard',
                'reading_time' => 5,
                'is_featured' => true,
                'is_trending' => true,
                'published_at' => now()->subDays(2),
                'status' => 1,
            ],
            [
                'user_id' => $editor->id,
                'category_id' => $cultureCategory->id,
                'title' => 'The Renaissance of Vinyl Records in the Digital Age',
                'slug' => 'the-renaissance-of-vinyl-records-in-the-digital-age',
                'excerpt' => 'Why physical music formats are making a comeback among younger generations.',
                'content' => '<p>In an era dominated by streaming services and digital downloads, vinyl records have experienced a remarkable resurgence. This isn\'t just nostalgia — it\'s a cultural movement that speaks to our desire for tangible, meaningful experiences.</p><p>Record stores are thriving, pressing plants are operating at full capacity, and younger listeners are discovering the warmth and character that only vinyl can provide.</p>',
                'type' => 'article',
                'format' => 'standard',
                'reading_time' => 4,
                'is_featured' => true,
                'published_at' => now()->subDays(5),
                'status' => 1,
            ],
            [
                'user_id' => $author->id,
                'category_id' => $businessCategory->id,
                'title' => 'Sustainable Startups: The New Wave of Entrepreneurship',
                'slug' => 'sustainable-startups-the-new-wave-of-entrepreneurship',
                'excerpt' => 'How eco-conscious businesses are reshaping the corporate landscape.',
                'content' => '<p>A new generation of entrepreneurs is proving that profitability and sustainability can go hand in hand. These innovative startups are not just building businesses — they\'re building a better future.</p><p>From renewable energy to circular economy models, these companies are demonstrating that the most successful businesses of tomorrow will be those that prioritize people and planet alongside profit.</p>',
                'type' => 'article',
                'format' => 'standard',
                'reading_time' => 6,
                'is_featured' => true,
                'is_trending' => true,
                'published_at' => now()->subWeek(),
                'status' => 1,
            ],
            [
                'user_id' => $admin->id,
                'category_id' => $scienceCategory->id,
                'title' => 'The Search for Extraterrestrial Life: Latest Discoveries',
                'slug' => 'the-search-for-extraterrestrial-life-latest-discoveries',
                'excerpt' => 'Recent discoveries in astrobiology and the ongoing search for life beyond Earth.',
                'content' => '<p>The question of whether we are alone in the universe has captivated humanity for centuries. Recent advances in technology and space exploration have brought us closer than ever to finding an answer.</p><p>From the discovery of exoplanets in the habitable zones of distant stars to the detection of organic molecules on Mars, the evidence is mounting that life may exist elsewhere in the cosmos.</p>',
                'type' => 'article',
                'format' => 'standard',
                'reading_time' => 7,
                'published_at' => now()->subDays(3),
                'status' => 1,
            ],
            [
                'user_id' => $editor->id,
                'category_id' => $lifestyleCategory->id,
                'title' => 'The Art of Mindful Living: A Guide to Modern Wellness',
                'slug' => 'the-art-of-mindful-living-a-guide-to-modern-wellness',
                'excerpt' => 'Practical tips for incorporating mindfulness into your daily routine.',
                'content' => '<p>In our fast-paced, always-connected world, the practice of mindfulness has never been more important. This guide explores simple yet powerful techniques for cultivating presence and peace in everyday life.</p><p>From meditation and breathwork to digital detoxes and nature therapy, discover how small changes can lead to profound improvements in your mental and physical well-being.</p>',
                'type' => 'article',
                'format' => 'standard',
                'reading_time' => 5,
                'published_at' => now()->subDays(1),
                'status' => 1,
            ],
        ];

        foreach ($posts as $postData) {
            $post = Post::create($postData);

            // Attach random tags
            $randomTags = Tag::inRandomOrder()->limit(3)->pluck('id')->toArray();
            $post->tags()->attach($randomTags);
        }
    }
}
