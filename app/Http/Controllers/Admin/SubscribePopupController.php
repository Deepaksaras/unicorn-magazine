<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\Subscriber;
use App\Support\SiteSettings;
use App\Support\Subscription;
use Illuminate\Http\Request;

/**
 * Admin → Website → Subscribe Pop-up
 */
class SubscribePopupController extends Controller
{
    public function edit()
    {
        $sources = Subscriber::where('status', 1)
            ->selectRaw("COALESCE(NULLIF(source, ''), 'website') as src, COUNT(*) as c")
            ->groupBy('src')
            ->orderByDesc('c')
            ->pluck('c', 'src');

        return view('admin.subscribe-popup.edit', [
            'settings' => Subscription::settings(),
            'sources' => $sources,
            'total' => $sources->sum(),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'subpop_delay' => 'required|integer|min:0|max:600',
            'subpop_interval' => 'required|in:' . implode(',', array_keys(Subscription::INTERVALS)),
            'google_client_id' => 'nullable|string|max:255',
            'google_client_secret' => 'nullable|string|max:255',
            'linkedin_client_id' => 'nullable|string|max:255',
            'linkedin_client_secret' => 'nullable|string|max:255',
        ]);

        $data['subpop_enabled'] = $request->boolean('subpop_enabled') ? '1' : '0';

        // Secrets are never shown again: an empty box keeps the saved one, "Remove" clears it
        foreach (['google', 'linkedin'] as $provider) {
            $key = $provider . '_client_secret';
            if ($request->boolean($provider . '_remove')) {
                $data[$provider . '_client_id'] = '';
                $data[$key] = '';
            } elseif (($data[$key] ?? '') === '' || $data[$key] === null) {
                unset($data[$key]);
            }
        }

        foreach ($data as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => trim((string) $value), 'group' => 'subscribe_popup', 'label' => Subscription::FIELDS[$key][0] ?? $key, 'type' => 'string', 'status' => 1]
            );
        }

        SiteSettings::flush();

        return redirect()->route('admin.subscribe-popup.edit')->with('success', 'Subscribe pop-up settings saved.');
    }
}
