<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Support\ImageUploader;
use App\Support\SiteSettings;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    /**
     * Every setting the website reads, grouped into tabs.
     * type: text, textarea, email, url, image, code
     */
    public static function schema(): array
    {
        return [
            'general' => ['label' => 'General', 'icon' => 'ri-settings-4-line', 'fields' => [
                'site_name' => ['Site name', 'text', 'The Unicorn Magazine'],
                'site_tagline' => ['Tagline', 'text', 'Digital Publishing Platform'],
                'site_description' => ['Site description', 'textarea', ''],
                'site_logo' => ['Logo', 'image', '', 'SVG or PNG. Empty = /img/logo.svg'],
                'site_favicon' => ['Favicon', 'image', '', 'Empty = /img/favicon.svg'],
                'admin_email' => ['Admin email', 'email', ''],
                'timezone' => ['Timezone', 'text', 'Asia/Kolkata'],
            ]],
            'header' => ['label' => 'Header & Footer', 'icon' => 'ri-layout-top-2-line', 'fields' => [
                'breaking_label' => ['News bar label', 'text', 'IMPORTANT', 'Headlines are managed in Breaking News.'],
                'subscribe_button_text' => ['Subscribe button text', 'text', 'Subscribe'],
                'header_menu_limit' => ['Header menu: links shown before “More”', 'text', '6', 'e.g. 6 = the first 6 Header Menu links show in the bar, the rest open from the “More” button. 0 = show all links (no More button).'],
                'header_more_text' => ['“More” button text', 'text', 'More'],
                'search_label' => ['Search overlay label', 'text', 'Search the publication'],
                'offcanvas_heading' => ['Side panel heading', 'text', 'Explore'],
                'offcanvas_title' => ['Side panel title', 'text', 'The Unicorn Magazine'],
                'offcanvas_text' => ['Side panel text', 'textarea', ''],
                'footer_description' => ['Footer description', 'textarea', ''],
                'newsletter_title' => ['Newsletter heading', 'text', 'Stay Updated'],
                'newsletter_text' => ['Newsletter text', 'textarea', 'Get the latest stories and insights delivered to your inbox.'],
                'footer_feature_kicker' => ['Footer feature kicker', 'text', 'THE UNICORN MAGAZINE'],
                'footer_feature_title' => ['Footer feature title', 'text', 'Ideas that shape tomorrow.'],
                'footer_feature_url' => ['Footer feature link', 'text', '/about'],
                'copyright_text' => ['Copyright line', 'text', '© {year} The Unicorn Magazine. All rights reserved.', 'Use {year} for the current year.'],
            ]],
            'subscribe' => ['label' => 'Subscribe pop-up', 'icon' => 'ri-mail-star-line', 'fields' => [
                'subscribe_modal_kicker' => ['Kicker', 'text', 'THE UNICORN MAGAZINE'],
                'subscribe_modal_title' => ['Title', 'text', 'Stay ahead of the curve.'],
                'subscribe_modal_text' => ['Text', 'textarea', 'Get the latest stories, startup insights, business news and ideas delivered to your inbox.'],
                'google_login_url' => ['“Continue with Google” link', 'url', '', 'Optional. The button is hidden until a link is set (needs a social-login package).'],
                'linkedin_login_url' => ['“Continue with LinkedIn” link', 'url', '', 'Optional, as above.'],
            ]],
            'contact' => ['label' => 'Contact', 'icon' => 'ri-contacts-book-3-line', 'fields' => [
                'contact_email' => ['General email', 'email', 'hello@theunicornmagazine.com', 'Shown on Privacy / Terms pages.'],
                'editorial_email' => ['Editorial email', 'email', 'editorial@theunicornmagazine.com'],
                'contact_phone' => ['Phone', 'text', ''],
                'contact_address' => ['Location', 'text', 'Mumbai, Maharashtra, India'],
            ]],
            'social' => ['label' => 'Social', 'icon' => 'ri-share-line', 'fields' => [
                'linkedin_url' => ['LinkedIn', 'url', ''],
                'facebook_url' => ['Facebook', 'url', ''],
                'instagram_url' => ['Instagram', 'url', ''],
                'twitter_url' => ['X / Twitter', 'url', ''],
                'youtube_url' => ['YouTube', 'url', '', 'Empty links are hidden on the site.'],
            ]],
            'seo' => ['label' => 'Tracking & Code', 'icon' => 'ri-code-s-slash-line', 'fields' => [
                'google_analytics_id' => ['Google Analytics ID', 'text', '', 'e.g. G-XXXXXXX'],
                'header_code' => ['Code before </head>', 'code', '', 'e.g. the Google AdSense script.'],
                'footer_code' => ['Code before </body>', 'code', ''],
            ]],
        ];
    }

    public function index()
    {
        return view('admin.settings.index', ['schema' => self::schema(), 'values' => SiteSettings::all()]);
    }

    public function update(Request $request)
    {
        $schema = self::schema();
        $rules = [];

        foreach ($schema as $group) {
            foreach ($group['fields'] as $key => [$label, $type]) {
                $rules[$key] = match ($type) {
                    'email' => 'nullable|email|max:191',
                    'url' => 'nullable|url|max:255',
                    'image' => 'nullable|string|max:255',
                    'code' => 'nullable|string|max:20000',
                    default => 'nullable|string|max:5000',
                };
                if ($type === 'image') {
                    $rules[$key . '_file'] = 'nullable|image|mimes:jpeg,jpg,png,gif,webp,svg,ico|max:4096';
                }
            }
        }

        $request->validate($rules);
        $current = SiteSettings::all();

        foreach ($schema as $groupKey => $group) {
            foreach ($group['fields'] as $key => [$label, $type]) {
                if (!$request->has($key) && !$request->hasFile($key . '_file')) {
                    continue;
                }

                $value = $request->input($key);

                if ($type === 'image') {
                    if ($file = $request->file($key . '_file')) {
                        ImageUploader::delete($current[$key] ?? null);
                        $value = $file->getClientOriginalExtension() === 'svg' || $file->getClientOriginalExtension() === 'ico'
                            ? $file->store('branding', 'public')
                            : ImageUploader::store($file, 'branding', 800);
                    } elseif ($request->boolean($key . '_remove')) {
                        ImageUploader::delete($current[$key] ?? null);
                        $value = null;
                    }
                }

                Setting::updateOrCreate(
                    ['key' => $key],
                    ['value' => $value, 'group' => $groupKey, 'label' => $label, 'type' => $type === 'code' ? 'text' : 'string', 'status' => 1]
                );
            }
        }

        SiteSettings::flush();

        return redirect()->route('admin.settings.index', ['tab' => $request->input('tab')])->with('success', 'Settings saved.');
    }
}
