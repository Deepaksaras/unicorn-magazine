<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Support\ImageUploader;
use App\Support\Seo;
use App\Support\SiteSettings;
use Illuminate\Http\Request;

/**
 * Admin → System → SEO
 * Site-wide default tags + the listing pages that have no edit screen of their own
 * (Latest News, Reports, Stories & Profiles, Search, Archive).
 * Articles, categories, tags, profiles, reports and pages have their own "SEO" box.
 */
class SeoController extends Controller
{
    /** Site-wide defaults:  key => [label, type] */
    public const DEFAULTS = [
        'default_meta_title' => ['Home page / default title', 'text'],
        'default_meta_description' => ['Default description', 'textarea'],
        'default_meta_keywords' => ['Default keywords', 'text'],
        'default_og_image' => ['Default share image', 'image'],
        'seo_twitter_site' => ['X / Twitter username', 'text'],
    ];

    public function edit()
    {
        return view('admin.seo.edit', ['values' => SiteSettings::all(), 'pages' => Seo::PAGES]);
    }

    public function update(Request $request)
    {
        $fields = self::DEFAULTS;
        foreach (Seo::PAGES as $key => [$label]) {
            $fields["seo_{$key}_title"] = [$label . ' – SEO title', 'text'];
            $fields["seo_{$key}_description"] = [$label . ' – SEO description', 'textarea'];
            $fields["seo_{$key}_keywords"] = [$label . ' – SEO keywords', 'text'];
            $fields["seo_{$key}_image"] = [$label . ' – share image', 'image'];
        }

        $rules = [];
        foreach ($fields as $key => [, $type]) {
            $rules[$key] = $type === 'textarea' ? 'nullable|string|max:500' : 'nullable|string|max:255';
            if ($type === 'image') {
                $rules[$key . '_file'] = 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:4096';
            }
        }
        $request->validate($rules);

        $current = SiteSettings::all();

        foreach ($fields as $key => [$label, $type]) {
            $value = $request->input($key);

            if ($type === 'image') {
                if ($file = $request->file($key . '_file')) {
                    ImageUploader::delete($current[$key] ?? null);
                    $value = ImageUploader::store($file, 'seo', 1200);
                } elseif ($request->boolean($key . '_remove')) {
                    ImageUploader::delete($current[$key] ?? null);
                    $value = null;
                } elseif (!$request->has($key)) {
                    $value = $current[$key] ?? null;
                }
            }

            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value !== null ? trim((string) $value) : null, 'group' => 'seo', 'label' => $label, 'type' => $type === 'textarea' ? 'text' : 'string', 'status' => 1]
            );
        }

        SiteSettings::flush();

        return redirect()->route('admin.seo.edit')->with('success', 'SEO settings saved.');
    }
}
