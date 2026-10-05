<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // General settings
            ['group' => 'general', 'key' => 'site_name', 'value' => 'The Unicorn Magazine', 'type' => 'string', 'label' => 'Site Name', 'is_public' => true],
            ['group' => 'general', 'key' => 'site_tagline', 'value' => 'Digital Publishing Platform', 'type' => 'string', 'label' => 'Site Tagline', 'is_public' => true],
            ['group' => 'general', 'key' => 'site_description', 'value' => 'A premier digital publishing platform bringing you the latest insights on technology, culture, business, science, and lifestyle.', 'type' => 'text', 'label' => 'Site Description', 'is_public' => true],
            ['group' => 'general', 'key' => 'admin_email', 'value' => 'admin@unicornmagazine.com', 'type' => 'string', 'label' => 'Admin Email', 'is_public' => false],
            ['group' => 'general', 'key' => 'timezone', 'value' => 'Asia/Kolkata', 'type' => 'string', 'label' => 'Timezone', 'is_public' => false],
            ['group' => 'general', 'key' => 'date_format', 'value' => 'M d, Y', 'type' => 'string', 'label' => 'Date Format', 'is_public' => false],
            ['group' => 'general', 'key' => 'currency', 'value' => 'INR', 'type' => 'string', 'label' => 'Currency', 'is_public' => false],
            ['group' => 'general', 'key' => 'currency_symbol', 'value' => '₹', 'type' => 'string', 'label' => 'Currency Symbol', 'is_public' => true],

            // Social media
            ['group' => 'social', 'key' => 'facebook_url', 'value' => 'https://facebook.com/unicornmagazine', 'type' => 'string', 'label' => 'Facebook URL', 'is_public' => true],
            ['group' => 'social', 'key' => 'twitter_url', 'value' => 'https://twitter.com/unicornmagazine', 'type' => 'string', 'label' => 'Twitter URL', 'is_public' => true],
            ['group' => 'social', 'key' => 'instagram_url', 'value' => 'https://instagram.com/unicornmagazine', 'type' => 'string', 'label' => 'Instagram URL', 'is_public' => true],
            ['group' => 'social', 'key' => 'linkedin_url', 'value' => 'https://linkedin.com/company/unicornmagazine', 'type' => 'string', 'label' => 'LinkedIn URL', 'is_public' => true],

            // SEO defaults
            ['group' => 'seo', 'key' => 'default_meta_title', 'value' => 'The Unicorn Magazine - Digital Publishing Platform', 'type' => 'string', 'label' => 'Default Meta Title', 'is_public' => false],
            ['group' => 'seo', 'key' => 'default_meta_description', 'value' => 'Your premier source for technology, culture, business, science, and lifestyle content.', 'type' => 'text', 'label' => 'Default Meta Description', 'is_public' => false],
            ['group' => 'seo', 'key' => 'google_analytics_id', 'value' => '', 'type' => 'string', 'label' => 'Google Analytics ID', 'is_public' => false],

            // Contact
            ['group' => 'contact', 'key' => 'contact_email', 'value' => 'contact@unicornmagazine.com', 'type' => 'string', 'label' => 'Contact Email', 'is_public' => true],
            ['group' => 'contact', 'key' => 'contact_phone', 'value' => '+91 9876543210', 'type' => 'string', 'label' => 'Contact Phone', 'is_public' => true],
            ['group' => 'contact', 'key' => 'contact_address', 'value' => 'Mumbai, Maharashtra, India', 'type' => 'text', 'label' => 'Contact Address', 'is_public' => true],
        ];

        foreach ($settings as $setting) {
            Setting::create(array_merge($setting, ['status' => 1]));
        }
    }
}
