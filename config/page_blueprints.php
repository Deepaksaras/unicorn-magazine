<?php

/*
|--------------------------------------------------------------------------
| Page Blueprints
|--------------------------------------------------------------------------
|
| Every public page that has fixed, designed sections is described here.
| The admin "Pages" editor builds its forms from these definitions and the
| public views read the saved values back (falling back to the defaults
| below, which are the texts from the original static HTML design).
|
| Field types: text, textarea, richtext, image, url, email, icon, number,
|              lines (one item per line), select, toggle, repeater
|
*/

$unsplash = fn (string $id, int $w = 1200) => "https://images.unsplash.com/{$id}?auto=format&fit=crop&w={$w}&q=85";

$categorySelect = fn (string $default) => [
    'type' => 'category',
    'label' => 'Posts from category',
    'default' => $default,
    'help' => 'Which category feeds this section.',
];

// "Headline lines" settings shared by every Home section (0 = no limit). Only the display is shortened, never the saved title.
$titleLines = fn (int $desktop = 3, int $mobile = 3) => [
    'title_lines_desktop' => ['type' => 'number', 'label' => 'Headline lines – large screens', 'default' => $desktop, 'help' => 'Longer headlines end with "…". 0 = no limit. The title itself is never changed.'],
    'title_lines_mobile' => ['type' => 'number', 'label' => 'Headline lines – mobile', 'default' => $mobile, 'help' => '0 = no limit.'],
];

$legalContact = [
    'label' => 'Contact block',
    'fields' => [
        'title' => ['type' => 'text', 'label' => 'Heading', 'default' => 'Contact Us'],
        'text' => ['type' => 'textarea', 'label' => 'Text', 'default' => 'If you have questions, concerns or requests relating to this page, please contact our team.'],
        'general_label' => ['type' => 'text', 'label' => 'General enquiries label', 'default' => 'General Enquiries'],
        'editorial_label' => ['type' => 'text', 'label' => 'Editorial label', 'default' => 'Editorial'],
        'location_label' => ['type' => 'text', 'label' => 'Location label', 'default' => 'Location'],
    ],
    'help' => 'Email addresses and location come from Settings → Contact.',
];

return [

    /* =====================================================================
     | HOME
     ===================================================================== */
    'home' => [
        'title' => 'Home Page',
        'slug' => 'home',
        'route' => 'home',
        'icon' => 'ri-home-5-line',
        'description' => 'Section titles, which categories feed each block, the Entrepreneurs feature and the Reports sidebar.',
        'sections' => [
            'latest' => [
                'label' => 'Latest News',
                'help' => 'Big story + 2 side stories use posts marked "Featured"; the strip below shows the newest posts.',
                'fields' => [
                    'title' => ['type' => 'text', 'label' => 'Title', 'default' => 'Latest News'],
                    'big_text' => ['type' => 'text', 'label' => 'Background text', 'default' => 'LATEST'],
                    'link' => ['type' => 'url', 'label' => '"View all" link', 'default' => '/latest'],
                    'strip_count' => ['type' => 'number', 'label' => 'Posts in bottom strip', 'default' => 6],
                    ...$titleLines(3, 3),
                ],
            ],
            'funding' => [
                'label' => 'Funding Spotlight',
                'fields' => [
                    'title' => ['type' => 'text', 'label' => 'Title', 'default' => 'Funding Spotlight'],
                    'big_text' => ['type' => 'text', 'label' => 'Background text', 'default' => 'FUNDING'],
                    'category' => $categorySelect('funding-spotlight'),
                    'count' => ['type' => 'number', 'label' => 'Number of posts', 'default' => 4],
                    ...$titleLines(3, 3),
                ],
            ],
            'business' => [
                'label' => 'Business Brief',
                'fields' => [
                    'title' => ['type' => 'text', 'label' => 'Title', 'default' => 'Business Brief'],
                    'big_text' => ['type' => 'text', 'label' => 'Background text', 'default' => 'BUSINESS'],
                    'category' => $categorySelect('business'),
                    'count' => ['type' => 'number', 'label' => 'Number of posts', 'default' => 5],
                    ...$titleLines(3, 2),
                ],
            ],
            'unicorn' => [
                'label' => "India's Unicorn",
                'fields' => [
                    'title' => ['type' => 'text', 'label' => 'Title', 'default' => "India's Unicorn"],
                    'big_text' => ['type' => 'text', 'label' => 'Background text', 'default' => 'UNICORN'],
                    'category' => $categorySelect('unicorn'),
                    ...$titleLines(3, 3),
                ],
            ],
            'startup' => [
                'label' => 'Startup Hustle',
                'fields' => [
                    'title' => ['type' => 'text', 'label' => 'Title', 'default' => 'Startup Hustle'],
                    'big_text' => ['type' => 'text', 'label' => 'Background text', 'default' => 'STARTUP'],
                    'category' => $categorySelect('startup'),
                    'count' => ['type' => 'number', 'label' => 'Number of posts', 'default' => 5],
                    'icons' => ['type' => 'lines', 'label' => 'Card icons (Remix icon class, one per line)', 'default' => ['ri-building-4-line', 'ri-lightbulb-flash-line', 'ri-rocket-2-line', 'ri-bar-chart-box-line', 'ri-team-line']],
                    ...$titleLines(3, 2),
                ],
            ],
            'entrepreneurs' => [
                'label' => 'Entrepreneurs feature',
                'fields' => [
                    'image' => ['type' => 'image', 'label' => 'Feature image', 'default' => 'https://marksmendaily.com/wp-content/uploads/2024/07/Deepi-Goyal.jpg'],
                    'eyebrow' => ['type' => 'text', 'label' => 'Eyebrow', 'default' => 'ENTREPRENEURS'],
                    'title' => ['type' => 'textarea', 'label' => 'Heading', 'default' => "The Minds.\nThe Vision.\nThe Impact."],
                    'description' => ['type' => 'textarea', 'label' => 'Description', 'default' => "Stories of entrepreneurs and leaders who are building India's future."],
                    'cta_text' => ['type' => 'text', 'label' => 'Button text', 'default' => 'Explore All Profiles'],
                    'cta_url' => ['type' => 'url', 'label' => 'Button link', 'default' => '/category/entrepreneurs'],
                    'quote_text' => ['type' => 'textarea', 'label' => 'Quote', 'default' => 'Focus on solving real problems. Everything else follows.'],
                    'quote_author' => ['type' => 'text', 'label' => 'Quote author', 'default' => 'Deepinder Goyal'],
                    'quote_role' => ['type' => 'text', 'label' => 'Quote author role', 'default' => 'Founder & CEO, Zomato'],
                    'stats' => [
                        'type' => 'repeater',
                        'label' => 'Stats',
                        'item_label' => 'Stat',
                        'fields' => [
                            'icon' => ['type' => 'icon', 'label' => 'Icon'],
                            'number' => ['type' => 'text', 'label' => 'Number'],
                            'label' => ['type' => 'textarea', 'label' => 'Label'],
                        ],
                        'default' => [
                            ['icon' => 'ri-group-line', 'number' => '250+', 'label' => "Entrepreneurs\nFeatured"],
                            ['icon' => 'ri-building-4-line', 'number' => '100+', 'label' => "Unicorn\nFounders"],
                            ['icon' => 'ri-trophy-line', 'number' => '45+', 'label' => "Billionaires\nFeatured"],
                            ['icon' => 'ri-global-line', 'number' => '20+', 'label' => "Industries\nRepresented"],
                        ],
                    ],
                    'spotlight_title' => ['type' => 'text', 'label' => 'Spotlight title', 'default' => 'ENTREPRENEURS'],
                    ...$titleLines(3, 3),
                    'category' => $categorySelect('entrepreneurs'),
                ],
            ],
            'billionaires' => [
                'label' => 'Billionaires',
                'fields' => [
                    'title' => ['type' => 'text', 'label' => 'Title', 'default' => 'Billionaires'],
                    'big_text' => ['type' => 'text', 'label' => 'Background text', 'default' => 'BILLIONAIRES'],
                    'category' => $categorySelect('billionaires'),
                    'count' => ['type' => 'number', 'label' => 'Number of posts', 'default' => 5],
                    ...$titleLines(2, 2),
                ],
            ],
            'profiles' => [
                'label' => 'Stories & Profiles',
                'help' => 'Cards come from the Profiles module.',
                'fields' => [
                    'title' => ['type' => 'text', 'label' => 'Title', 'default' => 'Stories & Profiles'],
                    'big_text' => ['type' => 'text', 'label' => 'Background text', 'default' => 'PROFILES'],
                    'link' => ['type' => 'url', 'label' => '"View all" link', 'default' => '/profiles'],
                    'count' => ['type' => 'number', 'label' => 'Number of profiles', 'default' => 5],
                    ...$titleLines(3, 3),
                ],
            ],
            'reports' => [
                'label' => 'Reports',
                'help' => 'The list comes from the Reports module. The exclusive card uses the report marked "Exclusive".',
                'fields' => [
                    'title' => ['type' => 'text', 'label' => 'Title', 'default' => 'Reports'],
                    'big_text' => ['type' => 'text', 'label' => 'Background text', 'default' => 'REPORTS'],
                    'count' => ['type' => 'number', 'label' => 'Reports in list', 'default' => 5],
                    ...$titleLines(3, 3),
                    'explore_text' => ['type' => 'text', 'label' => 'Explore button text', 'default' => 'Explore All Reports'],
                    'highlights_title' => ['type' => 'text', 'label' => 'Highlights title', 'default' => 'Report Highlights'],
                    'highlights' => [
                        'type' => 'repeater',
                        'label' => 'Highlights',
                        'item_label' => 'Highlight',
                        'fields' => [
                            'icon' => ['type' => 'icon', 'label' => 'Icon'],
                            'title' => ['type' => 'text', 'label' => 'Title'],
                            'text' => ['type' => 'textarea', 'label' => 'Text'],
                            'url' => ['type' => 'url', 'label' => 'Link'],
                        ],
                        'default' => [
                            ['icon' => 'ri-bar-chart-line', 'title' => 'Q1 2025 Funding Drops 12% YoY', 'text' => 'Early-stage deals remain resilient despite slowdown.', 'url' => '/reports'],
                            ['icon' => 'ri-building-2-line', 'title' => 'Bengaluru Leads Real Estate Demand', 'text' => 'Strong absorption seen in premium housing segment.', 'url' => '/reports'],
                            ['icon' => 'ri-leaf-line', 'title' => 'Renewable Capacity Crosses 200 GW', 'text' => 'India accelerates towards 2030 clean energy goals.', 'url' => '/reports'],
                        ],
                    ],
                    'exclusive_label' => ['type' => 'text', 'label' => 'Exclusive card label', 'default' => 'Exclusive'],
                    'exclusive_title' => ['type' => 'textarea', 'label' => 'Exclusive card title', 'default' => "India Startup\nFunding Report\n2025"],
                    'exclusive_text' => ['type' => 'textarea', 'label' => 'Exclusive card text', 'default' => 'Complete analysis of funding trends, key investors, and emerging sectors.'],
                    'exclusive_button' => ['type' => 'text', 'label' => 'Exclusive button text', 'default' => 'Download Report'],
                    'cover_top' => ['type' => 'text', 'label' => 'Cover: top line', 'default' => 'INDIA'],
                    'cover_main' => ['type' => 'textarea', 'label' => 'Cover: main text', 'default' => "STARTUP\nFUNDING"],
                    'cover_sub' => ['type' => 'text', 'label' => 'Cover: sub text', 'default' => 'REPORT'],
                    'cover_year' => ['type' => 'text', 'label' => 'Cover: year', 'default' => '2025'],
                ],
            ],
        ],
    ],

    /* =====================================================================
     | ABOUT
     ===================================================================== */
    'about' => [
        'title' => 'About Us',
        'slug' => 'about',
        'route' => 'about',
        'icon' => 'ri-information-line',
        'description' => 'Hero, intro, mission & vision, team heading and subscribe box. Team cards come from Team Members.',
        'sections' => [
            'hero' => [
                'label' => 'Hero',
                'fields' => [
                    'image' => ['type' => 'image', 'label' => 'Hero image', 'default' => $unsplash('photo-1497366754035-f200968a6e72', 2200)],
                    'overlay' => ['type' => 'text', 'label' => 'Overlay text', 'default' => 'ABOUT THE UNICORN MAGAZINE'],
                ],
            ],
            'intro' => [
                'label' => 'Who we are',
                'fields' => [
                    'label' => ['type' => 'text', 'label' => 'Side label', 'default' => 'WHO WE ARE'],
                    'heading' => ['type' => 'textarea', 'label' => 'Heading', 'default' => 'Stories behind the ideas shaping tomorrow.'],
                    'lead' => ['type' => 'textarea', 'label' => 'Lead paragraph', 'default' => 'The Unicorn Magazine is an independent editorial platform focused on the people, companies and ideas shaping the next generation of business and innovation.'],
                    'body' => ['type' => 'richtext', 'label' => 'Body', 'default' => '<p>We explore the stories behind ambitious founders, emerging companies, breakthrough technologies and the changing business landscape. Our goal is to go beyond headlines and bring readers closer to the people and ideas driving meaningful change.</p><p>From startup journeys and funding stories to technology, leadership and culture, we believe the most interesting stories are often found behind the numbers. We bring those perspectives together through thoughtful journalism, interviews and original editorial features.</p>'],
                ],
            ],
            'mission' => [
                'label' => 'Mission & Vision',
                'fields' => [
                    'title' => ['type' => 'text', 'label' => 'Section title', 'default' => 'What drives us'],
                    'mission_image' => ['type' => 'image', 'label' => 'Mission image', 'default' => 'https://media.easy-peasy.ai/8f1c8f91-b842-4b85-bc86-23d70d817fe1/9346c660-c291-4cad-a9a6-6dfbc0d85a0b.png'],
                    'mission_label' => ['type' => 'text', 'label' => 'Mission label', 'default' => 'OUR MISSION'],
                    'mission_heading' => ['type' => 'textarea', 'label' => 'Mission heading', 'default' => 'Make important stories worth discovering.'],
                    'mission_text' => ['type' => 'textarea', 'label' => 'Mission text', 'default' => 'Our mission is to create thoughtful, credible and engaging journalism that helps readers understand the businesses, people and ideas transforming the world around them.'],
                    'vision_image' => ['type' => 'image', 'label' => 'Vision image', 'default' => 'https://miro.medium.com/v2/resize%3Afit%3A1400/1%2AO8HvTTe1sj7vKb8smMIxbg.png'],
                    'vision_label' => ['type' => 'text', 'label' => 'Vision label', 'default' => 'OUR VISION'],
                    'vision_heading' => ['type' => 'textarea', 'label' => 'Vision heading', 'default' => 'Become a trusted window into tomorrow.'],
                    'vision_text' => ['type' => 'textarea', 'label' => 'Vision text', 'default' => 'We envision a publication that connects curious readers with the innovators, entrepreneurs and ideas that will define the next chapter of business, technology and society.'],
                ],
            ],
            'team' => [
                'label' => 'Team',
                'help' => 'People are managed in Team Members.',
                'fields' => [
                    'title' => ['type' => 'text', 'label' => 'Section title', 'default' => 'Meet our team'],
                ],
            ],
            'subscribe' => [
                'label' => 'Subscribe box',
                'fields' => [
                    'kicker' => ['type' => 'text', 'label' => 'Kicker', 'default' => 'THE UNICORN MAGAZINE'],
                    'heading' => ['type' => 'textarea', 'label' => 'Heading', 'default' => "Stay curious.\nStay ahead."],
                    'text' => ['type' => 'textarea', 'label' => 'Text', 'default' => 'Get the latest stories, insights and ideas shaping business, technology and the next generation of entrepreneurs — delivered to your inbox.'],
                    'note' => ['type' => 'text', 'label' => 'Small print', 'default' => 'By subscribing, you agree to receive editorial updates from The Unicorn Magazine.'],
                ],
            ],
        ],
    ],

    /* =====================================================================
     | CONTACT
     ===================================================================== */
    'contact' => [
        'title' => 'Contact',
        'slug' => 'contact',
        'route' => 'contact',
        'icon' => 'ri-mail-send-line',
        'description' => 'Intro, contact details, form texts and subject options. Messages arrive in Inbox → Contact Messages.',
        'sections' => [
            'intro' => [
                'label' => 'Intro',
                'fields' => [
                    'title' => ['type' => 'textarea', 'label' => 'Heading', 'default' => "Let's start a\nconversation."],
                    'text' => ['type' => 'textarea', 'label' => 'Intro text', 'default' => "Whether you have a story, business enquiry, partnership idea, or simply want to connect, we'd love to hear from you."],
                ],
            ],
            'details' => [
                'label' => 'Contact details',
                'fields' => [
                    'address_title' => ['type' => 'text', 'label' => 'Address title', 'default' => 'Address'],
                    'address' => ['type' => 'textarea', 'label' => 'Address', 'default' => "The Unicorn Magazine\nMumbai, Maharashtra\nIndia"],
                    'info_title' => ['type' => 'text', 'label' => 'Information title', 'default' => 'Information'],
                    'emails' => ['type' => 'lines', 'label' => 'Email addresses (one per line)', 'default' => ['hello@theunicornmagazine.com', 'editorial@theunicornmagazine.com']],
                    'hours_title' => ['type' => 'text', 'label' => 'Hours title', 'default' => 'Business Hours'],
                    'hours' => ['type' => 'textarea', 'label' => 'Business hours', 'default' => "Monday – Friday\n10:00 AM – 6:00 PM IST"],
                    'social_title' => ['type' => 'text', 'label' => 'Social title', 'default' => 'Social Media'],
                ],
                'help' => 'Social links come from Settings → Social.',
            ],
            'form' => [
                'label' => 'Contact form',
                'fields' => [
                    'heading' => ['type' => 'text', 'label' => 'Form heading', 'default' => "Tell us what's on your mind."],
                    'text' => ['type' => 'textarea', 'label' => 'Form text', 'default' => 'Fill in the details below and our team will get back to you as soon as possible.'],
                    'subjects' => ['type' => 'lines', 'label' => 'Subject options (one per line)', 'default' => ['Editorial Enquiry', 'Business Enquiry', 'Advertising', 'Partnership', 'Careers', 'Other']],
                    'consent' => ['type' => 'text', 'label' => 'Consent checkbox text', 'default' => 'I agree to the privacy policy and terms of use.'],
                    'button' => ['type' => 'text', 'label' => 'Button text', 'default' => 'Send Message'],
                    'success' => ['type' => 'text', 'label' => 'Success message', 'default' => 'Thank you for contacting The Unicorn Magazine. Our team will get back to you shortly.'],
                ],
            ],
        ],
    ],

    /* =====================================================================
     | CAREER
     ===================================================================== */
    'career' => [
        'title' => 'Careers',
        'slug' => 'career',
        'route' => 'career',
        'icon' => 'ri-briefcase-4-line',
        'description' => 'Hero, perks, openings heading and team voices. Jobs are managed in Job Openings.',
        'sections' => [
            'hero' => [
                'label' => 'Hero',
                'fields' => [
                    'kicker' => ['type' => 'text', 'label' => 'Kicker', 'default' => 'CAREERS AT THE UNICORN'],
                    'heading' => ['type' => 'textarea', 'label' => 'Heading', 'default' => "Build stories. Shape ideas. Create what's next."],
                    'text' => ['type' => 'textarea', 'label' => 'Text', 'default' => 'We are building a team of curious thinkers, creative storytellers and ambitious people who believe great journalism can influence how the world understands business, technology and innovation.'],
                    'button' => ['type' => 'text', 'label' => 'Button text', 'default' => 'See Open Roles'],
                    'image' => ['type' => 'image', 'label' => 'Image', 'default' => $unsplash('photo-1521737711867-e3b97375f902', 1600)],
                    'caption' => ['type' => 'text', 'label' => 'Image caption', 'default' => 'THE PEOPLE BEHIND THE STORIES'],
                ],
            ],
            'perks' => [
                'label' => 'Perks & Benefits',
                'fields' => [
                    'title' => ['type' => 'text', 'label' => 'Section title', 'default' => 'Perks & Benefits'],
                    'items' => [
                        'type' => 'repeater',
                        'label' => 'Perks',
                        'item_label' => 'Perk',
                        'fields' => [
                            'icon' => ['type' => 'icon', 'label' => 'Icon'],
                            'title' => ['type' => 'text', 'label' => 'Title'],
                            'text' => ['type' => 'textarea', 'label' => 'Text'],
                        ],
                        'default' => [
                            ['icon' => 'ri-time-line', 'title' => 'Flexible Work', 'text' => 'Flexible working arrangements that give our team the freedom to work productively and responsibly.'],
                            ['icon' => 'ri-book-open-line', 'title' => 'Keep Learning', 'text' => 'Access to learning opportunities, industry insights and experiences that help you grow professionally.'],
                            ['icon' => 'ri-heart-pulse-line', 'title' => 'Wellbeing First', 'text' => 'We encourage healthy boundaries and a working culture where wellbeing is treated as a priority.'],
                            ['icon' => 'ri-team-line', 'title' => 'Great People', 'text' => 'Work alongside curious editors, writers, designers and thinkers who enjoy building things together.'],
                            ['icon' => 'ri-lightbulb-flash-line', 'title' => 'Own Your Ideas', 'text' => 'Bring your perspective to the table and have the opportunity to turn ideas into meaningful work.'],
                            ['icon' => 'ri-rocket-2-line', 'title' => 'Grow With Us', 'text' => "Take on new challenges, expand your responsibilities and grow alongside a publication that's evolving."],
                        ],
                    ],
                ],
            ],
            'openings' => [
                'label' => 'Current openings',
                'help' => 'Jobs are managed in Job Openings. Without a job-specific apply link, "Apply" opens an email to the address below.',
                'fields' => [
                    'title' => ['type' => 'text', 'label' => 'Section title', 'default' => 'Current openings'],
                    'duties_label' => ['type' => 'text', 'label' => 'Responsibilities heading', 'default' => "What you'll do"],
                    'requirements_label' => ['type' => 'text', 'label' => 'Requirements heading', 'default' => "What we're looking for"],
                    'apply_text' => ['type' => 'text', 'label' => 'Apply button text', 'default' => 'Apply for this role'],
                    'apply_email' => ['type' => 'email', 'label' => 'Applications email', 'default' => 'careers@theunicornmagazine.com'],
                    'empty_text' => ['type' => 'text', 'label' => 'Text when there are no openings', 'default' => 'There are no open roles right now — please check back soon.'],
                ],
            ],
            'voices' => [
                'label' => 'Voices from our team',
                'fields' => [
                    'title' => ['type' => 'text', 'label' => 'Section title', 'default' => 'Voices from our team'],
                    'items' => [
                        'type' => 'repeater',
                        'label' => 'Testimonials',
                        'item_label' => 'Voice',
                        'fields' => [
                            'quote' => ['type' => 'textarea', 'label' => 'Quote'],
                            'name' => ['type' => 'text', 'label' => 'Name'],
                            'role' => ['type' => 'text', 'label' => 'Role'],
                        ],
                        'default' => [
                            ['quote' => 'What I love most is the freedom to follow a story wherever curiosity takes us. Every week brings a completely different perspective.', 'name' => 'Ananya Mehta', 'role' => 'Senior Editor'],
                            ['quote' => 'There is a real sense of ownership here. You are encouraged to bring your ideas forward and actually see them become part of the publication.', 'name' => 'Rahul Shah', 'role' => 'Technology Editor'],
                            ['quote' => 'The best part of working here is the people. We have different backgrounds and perspectives, but everyone is genuinely excited about creating great work.', 'name' => 'Sana Kapoor', 'role' => 'Digital Producer'],
                            ['quote' => 'I joined because of the editorial vision and stayed because there is always something new to learn. The pace keeps us challenged without losing creativity.', 'name' => 'Vikram Khanna', 'role' => 'Business Writer'],
                        ],
                    ],
                ],
            ],
        ],
    ],

    /* =====================================================================
     | ADVERTISE WITH US
     ===================================================================== */
    'advertise' => [
        'title' => 'Advertise with Us',
        'slug' => 'advertise-with-us',
        'route' => 'advertise',
        'icon' => 'ri-megaphone-line',
        'description' => 'Hero, audience numbers & charts, partnership options, brand logos and the enquiry form. Enquiries arrive in Inbox → Ad Enquiries.',
        'sections' => [
            'hero' => [
                'label' => 'Hero',
                'fields' => [
                    'image' => ['type' => 'image', 'label' => 'Hero image', 'default' => 'img/no1.webp'],
                    'heading' => ['type' => 'textarea', 'label' => 'Heading', 'default' => 'Put your brand in front of the people shaping tomorrow.'],
                ],
            ],
            'intro' => [
                'label' => 'Why The Unicorn',
                'fields' => [
                    'label' => ['type' => 'text', 'label' => 'Side label', 'default' => 'WHY THE UNICORN'],
                    'heading' => ['type' => 'textarea', 'label' => 'Heading', 'default' => "Reach an audience that doesn't just follow trends — they shape them."],
                    'lead' => ['type' => 'textarea', 'label' => 'Lead paragraph', 'default' => 'The Unicorn Magazine connects ambitious brands with entrepreneurs, business leaders, investors, innovators and curious minds who are actively building the future.'],
                    'body' => ['type' => 'textarea', 'label' => 'Paragraph', 'default' => 'Whether you are launching a product, building brand awareness, promoting an event or positioning your company as a leader in your industry, our advertising solutions help your message become part of the stories our audience cares about.'],
                ],
            ],
            'stats' => [
                'label' => 'Impact numbers',
                'fields' => [
                    'items' => [
                        'type' => 'repeater',
                        'label' => 'Numbers',
                        'item_label' => 'Number',
                        'fields' => [
                            'number' => ['type' => 'text', 'label' => 'Number'],
                            'label' => ['type' => 'text', 'label' => 'Label'],
                        ],
                        'default' => [
                            ['number' => '3Cr+', 'label' => 'Annual Reach'],
                            ['number' => '2.5Mn+', 'label' => 'Monthly Impressions'],
                            ['number' => '28L+', 'label' => 'Monthly Visitors'],
                            ['number' => '300+', 'label' => 'Brands'],
                        ],
                    ],
                ],
            ],
            'audience' => [
                'label' => 'Audience insights',
                'help' => 'Percentages drive the pie chart and progress bars automatically.',
                'fields' => [
                    'title' => ['type' => 'text', 'label' => 'Section title', 'default' => 'An audience that means business.'],
                    'seniority_title' => ['type' => 'text', 'label' => 'Chart 1 title', 'default' => 'Seniority'],
                    'seniority' => [
                        'type' => 'repeater',
                        'label' => 'Seniority split (pie, max 3)',
                        'item_label' => 'Slice',
                        'fields' => [
                            'label' => ['type' => 'text', 'label' => 'Label'],
                            'percent' => ['type' => 'number', 'label' => 'Percent'],
                        ],
                        'default' => [
                            ['label' => 'Founders, Investors & C Level', 'percent' => 48],
                            ['label' => 'Manager Level', 'percent' => 23],
                            ['label' => 'Entry Level', 'percent' => 29],
                        ],
                    ],
                    'company_title' => ['type' => 'text', 'label' => 'Chart 2 title', 'default' => 'Company Size'],
                    'company' => [
                        'type' => 'repeater',
                        'label' => 'Company size bars',
                        'item_label' => 'Bar',
                        'fields' => [
                            'label' => ['type' => 'text', 'label' => 'Label'],
                            'percent' => ['type' => 'number', 'label' => 'Percent'],
                        ],
                        'default' => [
                            ['label' => '1001+ Employees', 'percent' => 46],
                            ['label' => '501–1000 Employees', 'percent' => 13],
                            ['label' => '101–500 Employees', 'percent' => 7],
                            ['label' => '51–100 Employees', 'percent' => 11],
                            ['label' => '11–50 Employees', 'percent' => 15],
                            ['label' => '1–10 Employees', 'percent' => 9],
                        ],
                    ],
                    'age_title' => ['type' => 'text', 'label' => 'Chart 3 title', 'default' => 'Age Group'],
                    'age' => [
                        'type' => 'repeater',
                        'label' => 'Age group bars',
                        'item_label' => 'Bar',
                        'fields' => [
                            'label' => ['type' => 'text', 'label' => 'Label'],
                            'percent' => ['type' => 'number', 'label' => 'Percent'],
                        ],
                        'default' => [
                            ['label' => '65+', 'percent' => 3],
                            ['label' => '55–64', 'percent' => 5],
                            ['label' => '45–54', 'percent' => 4],
                            ['label' => '35–44', 'percent' => 13],
                            ['label' => '25–34', 'percent' => 42],
                            ['label' => '18–24', 'percent' => 31],
                        ],
                    ],
                ],
            ],
            'options' => [
                'label' => 'Ways to partner',
                'fields' => [
                    'title' => ['type' => 'text', 'label' => 'Section title', 'default' => 'Ways to partner with us'],
                    'button' => ['type' => 'text', 'label' => 'Link text', 'default' => 'Enquire Now'],
                    'items' => [
                        'type' => 'repeater',
                        'label' => 'Options',
                        'item_label' => 'Option',
                        'fields' => [
                            'icon' => ['type' => 'icon', 'label' => 'Icon'],
                            'title' => ['type' => 'text', 'label' => 'Title'],
                            'text' => ['type' => 'textarea', 'label' => 'Text'],
                        ],
                        'default' => [
                            ['icon' => 'ri-layout-4-line', 'title' => 'Display Advertising', 'text' => 'High-visibility placements designed to put your brand in front of engaged readers across the publication.'],
                            ['icon' => 'ri-article-line', 'title' => 'Sponsored Stories', 'text' => 'Tell your brand story through thoughtfully presented sponsored content created for an editorial audience.'],
                            ['icon' => 'ri-mail-send-line', 'title' => 'Newsletter & Digital', 'text' => 'Reach readers directly through newsletter placements and digital campaign opportunities.'],
                            ['icon' => 'ri-megaphone-line', 'title' => 'Custom Partnerships', 'text' => 'Build a campaign around your objectives with tailored partnerships combining multiple advertising formats.'],
                        ],
                    ],
                ],
            ],
            'brands' => [
                'label' => 'Brands we worked with',
                'fields' => [
                    'title' => ['type' => 'text', 'label' => 'Title', 'default' => 'Trusted by Brands, Making an Impact'],
                    'description' => ['type' => 'textarea', 'label' => 'Description', 'default' => 'From ambitious startups to established businesses, we help brands connect with an audience that values ideas, innovation and influence.'],
                    'logos' => [
                        'type' => 'repeater',
                        'label' => 'Logos',
                        'item_label' => 'Logo',
                        'fields' => [
                            'image' => ['type' => 'image', 'label' => 'Logo'],
                            'name' => ['type' => 'text', 'label' => 'Brand name'],
                        ],
                        'default' => array_map(fn ($n) => ['image' => "https://www.svgrepo.com/show/{$n[0]}.svg", 'name' => $n[1]], [
                            ['303172/linkedin-logo', 'LinkedIn'], ['303168/amazon-icon-logo', 'Amazon'], ['303155/under-armour-logo', 'Under Armour'],
                            ['303132/coca-cola-logo', 'Coca-Cola'], ['303143/microsoft-logo', 'Microsoft'], ['303152/tinder-1-logo', 'Tinder'],
                            ['303151/slack-logo', 'Slack'], ['303126/heineken-14-logo', 'Heineken'], ['303149/creative-cloud-cc-logo', 'Creative Cloud'],
                            ['303137/airbnb-2-logo', 'Airbnb'], ['303121/forbes-logo', 'Forbes'], ['303134/silver-star-1-logo', 'Silver Star'],
                            ['303135/soundcloud-logo', 'SoundCloud'], ['303130/snapchat-logo', 'Snapchat'], ['303107/facebook-messenger-3-logo', 'Messenger'],
                            ['303123/bmw-logo', 'BMW'], ['303113/facebook-icon-logo', 'Facebook'], ['303110/apple-black-logo', 'Apple'],
                            ['303106/mcdonald-s-15-logo', "McDonald's"], ['303108/google-icon-logo', 'Google'],
                        ]),
                    ],
                ],
            ],
            'form' => [
                'label' => 'Enquiry form',
                'fields' => [
                    'kicker' => ['type' => 'text', 'label' => 'Kicker', 'default' => "LET'S WORK TOGETHER"],
                    'heading' => ['type' => 'textarea', 'label' => 'Heading', 'default' => "Tell us about\nyour campaign."],
                    'text' => ['type' => 'textarea', 'label' => 'Text', 'default' => 'Have a campaign in mind or simply exploring possibilities? Tell us what you are looking for and our partnerships team will get back to you.'],
                    'email' => ['type' => 'email', 'label' => 'Partnerships email', 'default' => 'advertise@theunicornmagazine.com'],
                    'response_note' => ['type' => 'text', 'label' => 'Response time note', 'default' => 'We usually respond within 1–2 business days.'],
                    'industries' => ['type' => 'lines', 'label' => 'Industry options', 'default' => ['Technology', 'Finance', 'Healthcare', 'Consumer', 'Education', 'Professional Services', 'Real Estate', 'Other']],
                    'budgets' => ['type' => 'lines', 'label' => 'Budget options', 'default' => ['Under ₹1 Lakh', '₹1 – ₹5 Lakh', '₹5 – ₹10 Lakh', '₹10 Lakh+', 'Prefer to discuss']],
                    'interests' => ['type' => 'lines', 'label' => 'Advertising interest options', 'default' => ['Display Advertising', 'Sponsored Stories', 'Newsletter', 'Custom Partnership']],
                    'consent' => ['type' => 'text', 'label' => 'Consent text', 'default' => 'I agree to be contacted regarding advertising opportunities and partnerships.'],
                    'button' => ['type' => 'text', 'label' => 'Button text', 'default' => 'Send Enquiry'],
                    'success' => ['type' => 'text', 'label' => 'Success message', 'default' => 'Thank you for your enquiry. Our partnerships team will contact you shortly.'],
                ],
            ],
        ],
    ],

    /* =====================================================================
     | PRIVACY POLICY
     ===================================================================== */
    'privacy' => [
        'title' => 'Privacy Policy',
        'slug' => 'privacy-policy',
        'route' => 'privacy',
        'icon' => 'ri-shield-check-line',
        'description' => 'Page header and numbered policy sections. The "On this page" menu is built automatically.',
        'sections' => [
            'header' => [
                'label' => 'Page header',
                'fields' => [
                    'kicker' => ['type' => 'text', 'label' => 'Kicker', 'default' => 'THE UNICORN MAGAZINE'],
                    'title' => ['type' => 'text', 'label' => 'Title', 'default' => 'Privacy Policy'],
                    'intro' => ['type' => 'textarea', 'label' => 'Intro', 'default' => 'Your privacy matters to us. This Privacy Policy explains how The Unicorn Magazine collects, uses, protects and handles information when you visit or interact with our website.'],
                    'sidebar_label' => ['type' => 'text', 'label' => 'Sidebar label', 'default' => 'ON THIS PAGE'],
                ],
            ],
            'content' => [
                'label' => 'Policy sections',
                'fields' => [
                    'blocks' => [
                        'type' => 'repeater',
                        'label' => 'Sections',
                        'item_label' => 'Section',
                        'fields' => [
                            'title' => ['type' => 'text', 'label' => 'Heading'],
                            'body' => ['type' => 'richtext', 'label' => 'Content'],
                            'callout_title' => ['type' => 'text', 'label' => 'Callout title (optional)'],
                            'callout_text' => ['type' => 'textarea', 'label' => 'Callout text (optional)'],
                        ],
                        'default' => [
                            ['title' => 'Overview', 'body' => '<p>The Unicorn Magazine respects your privacy and is committed to handling your information responsibly. This Privacy Policy describes the types of information we may collect through our website and how that information may be used.</p><p>By using our website, subscribing to our publication, contacting us, or interacting with our services, you acknowledge the practices described in this policy.</p>'],
                            ['title' => 'Information We Collect', 'body' => '<p>Depending on how you interact with our website, we may collect information that you voluntarily provide to us.</p><h3>Information you provide</h3><ul><li>Name and contact information when you contact us.</li><li>Email address when you subscribe to our newsletter or publication updates.</li><li>Phone number or other details when voluntarily submitted through a form.</li><li>Information included in messages, enquiries, applications or other communications.</li></ul><h3>Information collected automatically</h3><p>When you visit our website, certain technical information may be collected automatically, such as browser type, device information, approximate location, pages viewed, referring pages and general usage data.</p>'],
                            ['title' => 'How We Use Information', 'body' => '<p>Information may be used to operate, maintain and improve The Unicorn Magazine and to communicate with readers, contributors, partners and businesses.</p><ul><li>Respond to enquiries and requests.</li><li>Deliver newsletters, updates and subscription communications.</li><li>Improve website content, functionality and user experience.</li><li>Understand general website usage and audience engagement.</li><li>Support advertising, partnerships and business enquiries.</li><li>Detect, prevent and address security or technical issues.</li></ul>', 'callout_title' => 'Our approach to privacy', 'callout_text' => 'We aim to collect only information that is reasonably necessary for the purpose for which it is provided or collected.'],
                            ['title' => 'Cookies & Tracking Technologies', 'body' => '<p>Our website may use cookies and similar technologies to remember preferences, understand website usage and improve functionality.</p><p>Cookies may be used for essential website functions, analytics, performance measurement or other features provided by third-party services.</p><p>You can generally control or disable cookies through your browser settings. Disabling certain cookies may affect some website functionality.</p>'],
                            ['title' => 'Sharing Information', 'body' => '<p>We do not intend to sell personal information simply because you visit our website. Information may, however, be shared where reasonably necessary to operate our services or comply with applicable obligations.</p><p>This may include sharing information with:</p><ul><li>Service providers that support website, communication or technology operations.</li><li>Analytics or infrastructure providers.</li><li>Professional advisers where appropriate.</li><li>Government authorities or other parties where required by applicable law.</li></ul>'],
                            ['title' => 'Data Security', 'body' => '<p>We take reasonable measures designed to protect information against unauthorized access, misuse, alteration or disclosure.</p><p>However, no website, online service or method of electronic transmission can be guaranteed to be completely secure.</p>'],
                            ['title' => 'Your Rights', 'body' => '<p>Depending on applicable law and your location, you may have rights relating to the personal information we hold about you.</p><p>These may include requesting access to, correction of, deletion of, or information about the processing of your personal information.</p><p>To make a privacy-related request, please contact us using the details provided below. We may need to verify your identity before processing certain requests.</p>'],
                            ['title' => 'Third-Party Links', 'body' => '<p>Our website may contain links to external websites, platforms or services. These third-party websites operate independently and may have their own privacy policies and practices.</p><p>We encourage you to review the privacy policies of external websites before providing them with personal information.</p>'],
                            ['title' => 'Changes to This Policy', 'body' => '<p>We may update this Privacy Policy from time to time to reflect changes to our website, services, technology or legal requirements.</p><p>When changes are made, the updated version will be published on this page along with a revised “Last Updated” date.</p>'],
                        ],
                    ],
                ],
            ],
            'contact' => $legalContact,
        ],
    ],

    /* =====================================================================
     | TERMS & CONDITIONS
     ===================================================================== */
    'terms' => [
        'title' => 'Terms & Conditions',
        'slug' => 'terms-conditions',
        'route' => 'terms',
        'icon' => 'ri-file-list-3-line',
        'description' => 'Page header and numbered terms sections. The "On this page" menu is built automatically.',
        'sections' => [
            'header' => [
                'label' => 'Page header',
                'fields' => [
                    'kicker' => ['type' => 'text', 'label' => 'Kicker', 'default' => 'THE UNICORN MAGAZINE'],
                    'title' => ['type' => 'text', 'label' => 'Title', 'default' => 'Terms & Conditions'],
                    'intro' => ['type' => 'textarea', 'label' => 'Intro', 'default' => 'These Terms & Conditions explain the rules and responsibilities that apply when you access or use The Unicorn Magazine website and its content.'],
                    'sidebar_label' => ['type' => 'text', 'label' => 'Sidebar label', 'default' => 'ON THIS PAGE'],
                ],
            ],
            'content' => [
                'label' => 'Terms sections',
                'fields' => [
                    'blocks' => [
                        'type' => 'repeater',
                        'label' => 'Sections',
                        'item_label' => 'Section',
                        'fields' => [
                            'title' => ['type' => 'text', 'label' => 'Heading'],
                            'body' => ['type' => 'richtext', 'label' => 'Content'],
                            'callout_title' => ['type' => 'text', 'label' => 'Callout title (optional)'],
                            'callout_text' => ['type' => 'textarea', 'label' => 'Callout text (optional)'],
                        ],
                        'default' => [
                            ['title' => 'Introduction', 'body' => '<p>Welcome to The Unicorn Magazine. These Terms &amp; Conditions govern your access to and use of our website, digital content, publications and related services.</p><p>By accessing or using the website, you agree to comply with these Terms &amp; Conditions. If you do not agree with these terms, please discontinue use of the website.</p>'],
                            ['title' => 'Acceptance of Terms', 'body' => '<p>By visiting, browsing or using The Unicorn Magazine website, you acknowledge that you have read, understood and agreed to these Terms &amp; Conditions.</p><p>These terms apply to all visitors, readers, subscribers, contributors, advertisers, partners and other users of the website.</p>', 'callout_title' => 'Please read before using our website', 'callout_text' => 'Your continued use of the website following any changes to these terms means that you accept the revised terms.'],
                            ['title' => 'Use of Our Website', 'body' => '<p>You may access and use our website for lawful, personal, informational and legitimate business purposes.</p><p>You agree not to use the website in a manner that could damage, disable, overburden or interfere with the operation of the website or the experience of other users.</p><p>You must not knowingly attempt to gain unauthorized access to systems, accounts, data or areas of the website that are not intended for public access.</p><h3>Prohibited activities</h3><ul><li>Using the website for unlawful or fraudulent purposes.</li><li>Attempting to disrupt or compromise website security.</li><li>Copying or reproducing content without appropriate permission.</li><li>Using automated systems to access or collect website content in a manner that is not permitted.</li><li>Misrepresenting your identity or affiliation with another person or organization.</li></ul>'],
                            ['title' => 'Intellectual Property', 'body' => '<p>Unless otherwise stated, the content published by The Unicorn Magazine, including articles, editorial copy, photographs, graphics, illustrations, logos, design elements and other original materials, is protected by applicable intellectual property laws.</p><p>The Unicorn Magazine name, branding and associated visual identity may not be reproduced, modified, distributed or used commercially without appropriate authorization.</p><p>You may access and share links to our published content for legitimate purposes, provided that the original source is appropriately credited and the content is not presented as your own.</p>'],
                            ['title' => 'Editorial Content', 'body' => '<p>The Unicorn Magazine publishes editorial content relating to business, technology, startups, entrepreneurs, innovation and other areas of interest to our readers.</p><p>Editorial content is provided for general informational purposes and should not automatically be interpreted as professional, financial, legal, investment or other specialized advice.</p><p>While we aim to provide accurate and useful information, circumstances may change after content has been published. Readers should independently evaluate information before relying on it for important decisions.</p>'],
                            ['title' => 'User Submissions', 'body' => '<p>If you submit information, story ideas, comments, feedback, photographs, materials or other content to us, you are responsible for ensuring that you have the necessary rights and permissions to provide that material.</p><p>You should not submit confidential, proprietary or sensitive information unless it is specifically requested and appropriate for the relevant purpose.</p><p>We may review, edit, decline or remove submitted material where appropriate or where required by applicable law.</p>'],
                            ['title' => 'Third-Party Links', 'body' => '<p>Our website may contain links to websites, applications, platforms or services operated by third parties.</p><p>These links may be provided for convenience, additional information or editorial context. The presence of a link does not necessarily mean that The Unicorn Magazine endorses or controls the third-party website.</p><p>Your use of third-party websites is subject to their respective terms, conditions and privacy policies.</p>'],
                            ['title' => 'Advertising & Partnerships', 'body' => '<p>The Unicorn Magazine may display advertisements, sponsored material, commercial partnerships or other promotional content on its website and publications.</p><p>Advertising and partnership arrangements may be subject to separate agreements between The Unicorn Magazine and the relevant advertiser or partner.</p><p>Where applicable, sponsored or commercial content may be identified in a manner intended to distinguish it from independent editorial content.</p>'],
                            ['title' => 'Disclaimer', 'body' => '<p>The website and its content are provided for general informational purposes. We make reasonable efforts to maintain the website and publish useful content, but we do not guarantee that every piece of information will always be complete, current or error-free.</p><p>Information published on the website should not be treated as a substitute for professional advice where professional advice is appropriate.</p><p>You are responsible for evaluating information and determining whether it is appropriate for your particular circumstances.</p>'],
                            ['title' => 'Limitation of Liability', 'body' => '<p>To the extent permitted by applicable law, The Unicorn Magazine and its owners, employees, contributors, service providers and partners will not be responsible for losses or damages arising from your use of, or reliance upon, the website or its content.</p><p>This includes, where legally permitted, direct, indirect, incidental or consequential losses arising from website access, interruptions, technical issues, third-party services or reliance on published information.</p><p>Nothing in these terms is intended to exclude or restrict any liability that cannot lawfully be excluded or restricted.</p>'],
                            ['title' => 'Changes to These Terms', 'body' => '<p>We may update these Terms &amp; Conditions from time to time to reflect changes to our website, services, business practices or applicable requirements.</p><p>The updated version will be published on this page with a revised “Last Updated” date.</p><p>Your continued use of the website after updated terms are published constitutes your acceptance of the revised terms, to the extent permitted by applicable law.</p>'],
                            ['title' => 'Governing Law', 'body' => '<p>These Terms &amp; Conditions shall be interpreted and applied in accordance with the applicable laws of the jurisdiction in which the relevant legal entity operating The Unicorn Magazine is established, unless otherwise required by applicable law.</p><p>Any disputes relating to the website or these terms shall be handled by the courts or authorities having appropriate jurisdiction.</p>'],
                        ],
                    ],
                ],
            ],
            'contact' => $legalContact,
        ],
    ],

    /* =====================================================================
     | 404 – PAGE NOT FOUND
     ===================================================================== */
    'not-found' => [
        'title' => 'Page Not Found (404)',
        'slug' => 'page-not-found',
        'route' => 'not-found',
        'icon' => 'ri-error-warning-line',
        'description' => 'What visitors see when a link is broken or a page does not exist.',
        'sections' => [
            'hero' => [
                'label' => 'Message',
                'fields' => [
                    'big_text' => ['type' => 'text', 'label' => 'Big outline text', 'default' => '404'],
                    'kicker' => ['type' => 'text', 'label' => 'Small label', 'default' => 'Error 404'],
                    'title' => ['type' => 'text', 'label' => 'Heading', 'default' => 'This story has gone off the record.'],
                    'text' => ['type' => 'textarea', 'label' => 'Text', 'default' => "The page you're looking for may have been moved, renamed or never existed. Let's get you back to the stories that matter."],
                    'primary_text' => ['type' => 'text', 'label' => 'Main button text', 'default' => 'Back to home'],
                    'primary_link' => ['type' => 'url', 'label' => 'Main button link', 'default' => '/'],
                    'secondary_text' => ['type' => 'text', 'label' => 'Second button text', 'default' => 'Read latest news'],
                    'secondary_link' => ['type' => 'url', 'label' => 'Second button link', 'default' => '/latest'],
                ],
            ],
            'search' => [
                'label' => 'Search box',
                'fields' => [
                    'label' => ['type' => 'text', 'label' => 'Label', 'default' => 'Or search the magazine'],
                    'placeholder' => ['type' => 'text', 'label' => 'Placeholder', 'default' => 'Search stories, founders, companies…'],
                    'button' => ['type' => 'text', 'label' => 'Button text', 'default' => 'Search'],
                ],
            ],
            'stories' => [
                'label' => 'Suggested stories',
                'help' => 'Shows the newest published articles.',
                'fields' => [
                    'title' => ['type' => 'text', 'label' => 'Title', 'default' => 'Worth reading instead'],
                    'big_text' => ['type' => 'text', 'label' => 'Background text', 'default' => 'READ'],
                    'count' => ['type' => 'number', 'label' => 'Number of stories', 'default' => 4],
                ],
            ],
        ],
    ],
];
