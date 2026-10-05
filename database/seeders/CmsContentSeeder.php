<?php

namespace Database\Seeders;

use App\Models\Advertisement;
use App\Models\AdvertisementPlacement;
use App\Models\Category;
use App\Models\JobOpening;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\Post;
use App\Models\Profile;
use App\Models\Report;
use App\Models\Role;
use App\Models\Setting;
use App\Models\TeamMember;
use App\Models\User;
use App\Support\SiteSettings;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Fills the CMS with the content of the original static HTML design.
 *
 *   php artisan db:seed --class=CmsContentSeeder
 *
 * Safe to run on an existing database and safe to run more than once:
 * it only creates what is missing and never overwrites your edits.
 */
class CmsContentSeeder extends Seeder
{
    protected function img(string $id, int $w = 1200): string
    {
        return "https://images.unsplash.com/{$id}?auto=format&fit=crop&w={$w}&q=85";
    }

    public function run(): void
    {
        $this->roles();
        $this->categories();
        $this->menus();
        $this->settings();
        $this->pages();
        $this->ads();
        $this->team();
        $this->jobs();
        $this->samplePosts();
        $this->profiles();
        $this->reports();

        SiteSettings::flush();
    }

    /* ------------------------------------------------------------------ */

    protected function roles(): void
    {
        $super = Role::firstOrCreate(['slug' => 'super-admin'], ['name' => 'Super Admin', 'description' => 'Full access', 'status' => 1]);
        Role::firstOrCreate(['slug' => 'editor'], ['name' => 'Editor', 'description' => 'Manage content', 'status' => 1]);
        Role::firstOrCreate(['slug' => 'author'], ['name' => 'Author', 'description' => 'Write articles', 'status' => 1]);

        // Make sure at least one user can open /admin
        if (!User::whereHas('roles', fn ($q) => $q->whereIn('slug', ['super-admin', 'admin']))->exists()) {
            $admin = User::where('email', 'admin@unicornmagazine.com')->first() ?? User::orderBy('id')->first();
            $admin?->roles()->syncWithoutDetaching([$super->id]);
        }
    }

    protected function categories(): void
    {
        $list = [
            ['funding-spotlight', 'Funding Spotlight', 'Funding rounds, investors and the capital fuelling Indian startups.'],
            ['business', 'Business', 'Business news, market analysis, and entrepreneurship.'],
            ['unicorn', 'Unicorn', "Inside India's billion-dollar startups."],
            ['startup', 'Startup', 'Founders, launches and the startup hustle.'],
            ['entrepreneurs', 'Entrepreneurs', "Stories of entrepreneurs and leaders who are building India's future."],
            ['billionaires', 'Billionaires', "The new generation of billionaires reshaping India's business landscape."],
            ['stories-profiles', 'Stories & Profiles', 'In-depth profiles of leaders and changemakers.'],
        ];

        foreach ($list as $i => [$slug, $name, $description]) {
            $category = Category::withDeleted()->firstOrCreate(
                ['slug' => $slug],
                ['name' => $name, 'description' => $description, 'position' => 10 + $i, 'status' => 1]
            );
            if ((int) $category->status === 4) {
                $category->update(['status' => 1]);
            }
        }
    }

    protected function menus(): void
    {
        $menus = [
            'header-menu' => ['Header Menu', 'header', [
                ['Home', '/'], ['Latest News', '/latest'], ['Funding Spotlight', '/category/funding-spotlight'],
                ['Business', '/category/business'], ['Unicorn', '/category/unicorn'], ['Startup', '/category/startup'],
                ['Entrepreneurs', '/category/entrepreneurs'], ['Billionaires', '/category/billionaires'],
                ['Stories & Profile', '/category/stories-profiles'], ['Reports', '/reports'],
            ]],
            'footer-stories' => ['Stories', 'footer', [
                ['Latest News', '/latest'], ['Funding Spotlight', '/category/funding-spotlight'], ['Business Brief', '/category/business'],
                ["India's Unicorns", '/category/unicorn'], ['Startup Hustle', '/category/startup'], ['Entrepreneurs', '/category/entrepreneurs'],
                ['Billionaires', '/category/billionaires'], ['Stories & Profiles', '/category/stories-profiles'], ['Reports', '/reports'],
            ]],
            'footer-company' => ['Company', 'footer', [
                ['About Us', '/about'], ['Advertise with Us', '/advertise-with-us'], ['Contact', '/contact'], ['Careers', '/career'],
            ]],
            'footer-legal' => ['Footer Legal', 'footer', [
                ['Privacy Policy', '/privacy-policy'], ['Terms & Conditions', '/terms-conditions'],
            ]],
            'offcanvas-menu' => ['Side Panel', 'offcanvas', [
                ['About Us', '/about'], ['Contact', '/contact'], ['Advertise with Us', '/advertise-with-us'],
            ]],
        ];

        foreach ($menus as $slug => [$name, $location, $items]) {
            $menu = Menu::withDeleted()->firstOrCreate(['slug' => $slug], ['name' => $name, 'location' => $location, 'status' => 1]);
            if ((int) $menu->status === 4) {
                $menu->update(['status' => 1]);
            }

            $active = $menu->items()->where('status', '!=', 4)->get();

            // Replace the original placeholder header menu (Technology, Culture, …)
            $isOldDefault = $slug === 'header-menu'
                && $active->pluck('url')->contains('/category/technology')
                && $active->pluck('url')->contains('/archive');

            if ($active->isNotEmpty() && !$isOldDefault) {
                continue;
            }

            MenuItem::where('menu_id', $menu->id)->update(['status' => 4]);

            foreach ($items as $i => [$title, $url]) {
                MenuItem::create(['menu_id' => $menu->id, 'title' => $title, 'url' => $url, 'target' => '_self', 'position' => $i + 1, 'status' => 1]);
            }
        }

        // The old combined footer menu is no longer used by the layout
        Menu::where('slug', 'footer-menu')->update(['status' => 0]);
    }

    protected function settings(): void
    {
        $defaults = [
            'general' => [
                'site_name' => 'The Unicorn Magazine',
                'site_description' => 'An independent editorial platform focused on the people, companies and ideas shaping the next generation of business and innovation.',
            ],
            'header' => [
                'breaking_label' => 'IMPORTANT',
                'subscribe_button_text' => 'Subscribe',
                'search_label' => 'Search the publication',
                'offcanvas_heading' => 'Explore',
                'offcanvas_title' => 'The Unicorn Magazine',
                'offcanvas_text' => 'The Unicorn Magazine is an independent editorial platform focused on the people, companies and ideas shaping the next generation of business and innovation.',
                'footer_description' => "The Unicorn Magazine brings you thoughtful stories, business insights, startup news, technology, culture and the people shaping India's future.",
                'newsletter_title' => 'Stay Updated',
                'newsletter_text' => 'Get the latest stories and insights delivered to your inbox.',
                'footer_feature_kicker' => 'THE UNICORN MAGAZINE',
                'footer_feature_title' => 'Ideas that shape tomorrow.',
                'footer_feature_url' => '/about',
                'copyright_text' => '© {year} The Unicorn Magazine. All rights reserved.',
            ],
            'subscribe' => [
                'subscribe_modal_kicker' => 'THE UNICORN MAGAZINE',
                'subscribe_modal_title' => 'Stay ahead of the curve.',
                'subscribe_modal_text' => 'Get the latest stories, startup insights, business news and ideas delivered to your inbox.',
            ],
            'contact' => [
                'contact_email' => 'hello@theunicornmagazine.com',
                'editorial_email' => 'editorial@theunicornmagazine.com',
                'contact_address' => 'Mumbai, Maharashtra, India',
            ],
            'social' => [
                'linkedin_url' => 'https://linkedin.com/company/unicornmagazine',
                'facebook_url' => 'https://facebook.com/unicornmagazine',
                'instagram_url' => 'https://instagram.com/unicornmagazine',
                'twitter_url' => 'https://twitter.com/unicornmagazine',
                'youtube_url' => 'https://youtube.com/@unicornmagazine',
            ],
            'seo' => [
                'default_meta_title' => 'The Unicorn Magazine',
            ],
        ];

        foreach ($defaults as $group => $values) {
            foreach ($values as $key => $value) {
                Setting::withDeleted()->firstOrCreate(['key' => $key], ['group' => $group, 'value' => $value, 'type' => 'string', 'status' => 1]);
            }
        }
    }

    protected function pages(): void
    {
        $admin = User::orderBy('id')->first();

        foreach (config('page_blueprints') as $key => $blueprint) {
            $page = Page::withDeleted()->firstOrCreate(
                ['slug' => $blueprint['slug']],
                ['title' => $blueprint['title'], 'template' => $key, 'user_id' => $admin?->id, 'published_at' => now(), 'status' => 1]
            );

            $position = 0;
            foreach ($blueprint['sections'] as $sectionKey => $section) {
                $values = collect($section['fields'])->map(fn ($f) => $f['default'] ?? null)->all();

                PageSection::withDeleted()->firstOrCreate(
                    ['page_id' => $page->id, 'section_name' => $sectionKey],
                    ['settings' => $values, 'layout' => $key, 'position' => $position++, 'status' => 1]
                );
            }
        }
    }

    protected function ads(): void
    {
        $placements = [
            'header-ad' => ['Header Ad', 'Banner under the navigation', '1200x200', 'header'],
            'in-content-ad' => ['In-content Ad', 'Banners inside lists and articles', '1200x200', 'in-content'],
            'sidebar-ad' => ['Sidebar Ad', 'Square ad in sidebars', '300x250', 'sidebar'],
        ];

        foreach ($placements as $slug => [$name, $description, $dimensions, $location]) {
            AdvertisementPlacement::withDeleted()->firstOrCreate(
                ['slug' => $slug],
                ['name' => $name, 'description' => $description, 'dimensions' => $dimensions, 'location' => $location, 'max_ads' => 1, 'is_active' => true, 'status' => 1]
            );
        }

        // The original seed ads pointed at "#": link them to the Advertise page
        Advertisement::whereIn('url', ['#', ''])->update(['url' => '/advertise-with-us', 'target' => '_self']);
    }

    protected function team(): void
    {
        if (TeamMember::where('status', '!=', 4)->exists()) {
            return;
        }

        $people = [
            ['Arjun Mehta', 'Editor in Chief', 'photo-1560250097-0b93528c311a'],
            ['Riya Kapoor', 'Senior Editor', 'photo-1580489944761-15a19d654956'],
            ['Karan Shah', 'Business Writer', 'photo-1500648767791-00dcc994a43e'],
            ['Ananya Rao', 'Technology Editor', 'photo-1551836022-d5d88e9218df'],
        ];

        foreach ($people as $i => [$name, $role, $photo]) {
            TeamMember::create([
                'name' => $name,
                'slug' => Str::slug($name),
                'role_title' => $role,
                'photo' => $this->img($photo, 900),
                'social_links' => ['linkedin' => 'https://linkedin.com', 'x' => 'https://x.com', 'instagram' => 'https://instagram.com'],
                'sort_order' => $i + 1,
                'status' => 1,
            ]);
        }
    }

    protected function jobs(): void
    {
        if (JobOpening::where('status', '!=', 4)->exists()) {
            return;
        }

        $jobs = [
            ['Senior Business Writer', 'Editorial', 'Full Time', 'Mumbai / Hybrid',
                'We are looking for an experienced writer who can turn complex business stories into compelling, accessible and insightful editorial pieces.',
                ['Research and write original business stories.', 'Interview founders and industry leaders.', 'Develop story ideas and editorial angles.'],
                ['Strong writing and storytelling skills.', '3+ years of relevant editorial experience.', 'Strong understanding of business and startups.']],
            ['Technology Editor', 'Editorial', 'Full Time', 'Bengaluru / Hybrid',
                'Help us explore the technologies, companies and people transforming how businesses and society operate.',
                ['Cover technology and innovation trends.', 'Develop interviews and feature stories.', 'Work with writers and editorial teams.'],
                ['Excellent editorial judgment.', 'Strong technology awareness.', 'Ability to simplify complex subjects.']],
            ['Digital Content Producer', 'Digital', 'Full Time', 'Remote',
                'Join our digital team and help transform editorial ideas into engaging experiences across our website and digital channels.',
                ['Plan and publish digital content.', 'Work with editorial and design teams.', 'Optimize stories for digital audiences.'],
                ['Strong digital content experience.', 'Excellent communication skills.', 'Interest in media and technology.']],
        ];

        foreach ($jobs as $i => [$title, $dept, $type, $location, $summary, $duties, $reqs]) {
            JobOpening::create([
                'title' => $title, 'slug' => Str::slug($title), 'department' => $dept, 'employment_type' => $type,
                'location' => $location, 'summary' => $summary, 'responsibilities' => $duties, 'requirements' => $reqs,
                'position' => $i + 1, 'status' => 1,
            ]);
        }
    }

    /**
     * Sample stories from the static design for every empty section category.
     */
    protected function samplePosts(): void
    {
        $users = User::orderBy('id')->pluck('id')->all();
        if (!$users) {
            return;
        }

        $body = fn (string $lead) => '<p class="post-intro">' . e($lead) . '</p>'
            . '<p>This is sample content imported from the original design. Replace it from Admin → Articles with your own reporting, interviews and analysis.</p>'
            . '<h2>Why it matters</h2><p>From startup journeys and funding stories to technology, leadership and culture, the most interesting stories are often found behind the numbers.</p>'
            . '<div class="post-callout"><div class="post-callout-icon"><i class="ri-double-quotes-l"></i></div><div><p>Innovation creates possibilities, but ethics determines whether those possibilities create meaningful and sustainable progress.</p></div></div>'
            . '<p>Use the editor to add images, headings, quotes and embedded media.</p>';

        $sets = [
            'funding-spotlight' => [
                ['Dovetail Capital Raises ₹100 Cr Series A Fund', 'photo-1556761175-b413da4baf72', 'Funding', true],
                ['Fintech Startup Secures ₹75 Cr in Fresh Funding', 'photo-1553877522-43269d4ea984', 'Funding', false],
                ['New-Age Startup Raises $12 Million in Series B', 'photo-1551836022-d5d88e9218df', 'Funding', false],
                ['Venture Capital Firm Backs Emerging Startup', 'photo-1556761175-5973dc0f32e7', 'Funding', false],
            ],
            'business' => [
                ['Why India\'s New Economy Is Growing Faster', 'photo-1556761175-b413da4baf72', 'Business', true],
                ['The Technologies Shaping Tomorrow\'s World', 'photo-1518770660439-4636190af475', 'Technology', true],
                ['The New Ideas Changing Modern Indian Culture', 'photo-1577083552431-6e5fd01aa342', 'Culture', false],
                ['Inside the Destinations Everyone Is Talking About', 'photo-1500530855697-b586d89ba3ee', 'Travel', false],
                ['Balancing Artificial Intelligence Ethics With Innovation and Progress', 'photo-1677442136019-21780ecad995', 'Business', false],
            ],
            'unicorn' => [
                ["India's Newest Unicorn Raises \$180 Million", 'photo-1556761175-b413da4baf72', 'Unicorn', false],
                ["Fintech Startup Joins India's Unicorn Club", 'photo-1556761175-5973dc0f32e7', 'Unicorn', false],
                ['SaaS Company Valuation Crosses $1 Billion', 'photo-1556761175-4b46a572b786', 'Unicorn', false],
                ['Emerging Startup Raises Fresh Growth Capital', 'photo-1521737711867-e3b97375f902', 'Unicorn', false],
            ],
            'startup' => [
                ['Adani Group to Invest ₹12,000 Cr in Odisha', 'photo-1560179707-f14e90ef3623', 'Business', false],
                ['Emerging Startup Raises ₹85 Cr to Scale Operations', 'photo-1556761175-b413da4baf72', 'Startups', false],
                ['Indian Startup Expands Into Global Markets', 'photo-1521737711867-e3b97375f902', 'Innovation', false],
                ['New Venture Targets Rapid Growth This Year', 'photo-1556761175-4b46a572b786', 'Growth', false],
                ["Founders Build India's Next Big Startup", 'photo-1556761175-5973dc0f32e7', 'Entrepreneurs', false],
            ],
            'entrepreneurs' => [
                ["Building India's Most Trusted Financial Platform", 'photo-1560250097-0b93528c311a', 'Interview', false],
                ['From Idea to Industry Leader', 'photo-1580489944761-15a19d654956', 'Leadership', false],
                ["Building India's EV Revolution", 'photo-1500648767791-00dcc994a43e', 'Success Story', false],
            ],
            'billionaires' => [
                ["The new generation of billionaires reshaping India's business landscape", 'photo-1560250097-0b93528c311a', 'Billionaires', false],
                ["How India's wealth creators are building businesses for the future", 'photo-1573496359142-b8d87734a5a2', 'Leadership', false],
                ['From first venture to billion-dollar empire: the founder playbook', 'photo-1500648767791-00dcc994a43e', 'Investments', false],
                ["Where the world's wealthiest are putting their money next", 'photo-1551836022-d5d88e9218df', 'Entrepreneurs', false],
                ['The leadership principles behind extraordinary business success', 'photo-1507003211169-0a1dd7228f2d', 'Wealth', false],
            ],
        ];

        $n = 0;
        foreach ($sets as $slug => $posts) {
            $category = Category::where('slug', $slug)->first();
            if (!$category || Post::where('category_id', $category->id)->where('status', '!=', 4)->exists()) {
                continue;
            }

            foreach ($posts as $i => [$title, $image, $badge, $featured]) {
                $postSlug = Str::slug($title);
                if (Post::withDeleted()->where('slug', $postSlug)->exists()) {
                    continue;
                }

                $excerpt = 'From technology and culture to business and society, discover the stories shaping the way we understand our changing world.';

                Post::create([
                    'user_id' => $users[$n % count($users)],
                    'category_id' => $category->id,
                    'title' => $title,
                    'slug' => $postSlug,
                    'excerpt' => $excerpt,
                    'content' => $body($excerpt),
                    'featured_image' => $this->img($image, 1400),
                    'badge' => $badge,
                    'reading_time' => 4,
                    'is_featured' => $featured,
                    'published_at' => now()->subHours(++$n * 7),
                    'status' => 1,
                ]);
            }
        }
    }

    protected function profiles(): void
    {
        if (Profile::where('status', '!=', 4)->exists()) {
            return;
        }

        $people = [
            ['Ratan Tata', 'Leadership', "A visionary business leader whose legacy continues to shape India's corporate world.", 'photo-1560250097-0b93528c311a', 'executive'],
            ['Falguni Nayar', 'Entrepreneurs', 'From investment banking to building a category-defining consumer brand in India.', 'photo-1573496359142-b8d87734a5a2', 'entrepreneur'],
            ['N. R. Narayana Murthy', 'Business', "A technology pioneer who helped establish India's global reputation in IT services.", 'photo-1551836022-d5d88e9218df', 'founder'],
            ['Kiran Mazumdar-Shaw', 'Women In Business', "A pioneering entrepreneur transforming India's biotechnology landscape globally.", 'photo-1580489944761-15a19d654956', 'entrepreneur'],
            ['Bhavish Aggarwal', 'Founders', "Building ambitious technology businesses while redefining India's mobility ecosystem.", 'photo-1507003211169-0a1dd7228f2d', 'founder'],
        ];

        foreach ($people as $i => [$name, $label, $summary, $photo, $type]) {
            Profile::create([
                'name' => $name, 'slug' => Str::slug($name), 'label' => $label, 'summary' => $summary,
                'profile_type' => $type, 'profile_image' => $this->img($photo, 700), 'currency' => 'INR',
                'position' => $i + 1, 'status' => 1,
            ]);
        }
    }

    protected function reports(): void
    {
        if (Report::where('status', '!=', 4)->exists()) {
            return;
        }

        $user = User::orderBy('id')->first();
        if (!$user) {
            return;
        }

        $reports = [
            ["India's Startup Funding Trends Q1 2025 Report", 'Economy', 'A comprehensive breakdown of funding patterns, key sectors, and investor activity in India.', 'photo-1556761175-b413da4baf72', '2025-05-24', true],
            ['Indian Real Estate Outlook 2025: Growth Hotspots', 'Real Estate', 'Key insights on top cities, price trends, demand, supply gap & future opportunities.', 'photo-1486406146926-c627a92ad1ab', '2025-05-22', false],
            ["India's Green Energy Transition Report 2025", 'Sustainability', 'Analyzing investments, policy shifts and the road to a sustainable future.', 'photo-1497435334941-8c899ee9e8e9', '2025-05-20', false],
        ];

        foreach ($reports as [$title, $label, $description, $cover, $date, $exclusive]) {
            Report::create([
                'user_id' => $user->id, 'title' => $title, 'slug' => Str::slug($title), 'description' => $description,
                'cover_image' => $this->img($cover, 1000), 'report_type' => 'research', 'category_label' => $label,
                'author_name' => 'Ananya Sharma', 'report_date' => $date, 'is_exclusive' => $exclusive, 'status' => 1,
            ]);
        }
    }
}
