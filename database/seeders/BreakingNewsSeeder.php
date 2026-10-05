<?php

namespace Database\Seeders;

use App\Models\BreakingNews;
use Illuminate\Database\Seeder;

class BreakingNewsSeeder extends Seeder
{
    public function run(): void
    {
        $news = [
            ['title' => "India's startup ecosystem sees strong funding momentum as new-age companies continue to scale.", 'position' => 1],
            ['title' => 'Dovetail Capital Raises ₹100 Cr Series A Fund to back early-stage startups.', 'position' => 2],
            ['title' => 'Fintech Startup Secures ₹75 Cr in Fresh Funding from global investors.', 'position' => 3],
            ['title' => 'New-Age Startup Raises $12 Million in Series B round led by Sequoia.', 'position' => 4],
        ];

        foreach ($news as $item) {
            BreakingNews::create([
                'title' => $item['title'],
                'url' => '#',
                'position' => $item['position'],
                'status' => 1,
            ]);
        }
    }
}
