<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use App\Models\Setting;
use App\Support\AdStats;
use App\Support\Popup;
use App\Support\SiteSettings;
use Illuminate\Http\Request;

/**
 * Admin → Advertising → Pop-up Ad
 */
class PopupController extends Controller
{
    public function edit()
    {
        $placement = Popup::placement();

        $ads = $placement
            ? Advertisement::where('placement_id', $placement->id)->where('status', '!=', 4)->latest('id')->get()
            : collect();

        return view('admin.popup.edit', [
            'settings' => Popup::settings(),
            'placement' => $placement,
            'ads' => $ads,
            'stats' => AdStats::totals($ads->pluck('id')->all(), null, null),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'popup_delay' => 'required|integer|min:0|max:120',
            'popup_scroll' => 'required|integer|min:0|max:100',
            'popup_close_after' => 'required|integer|min:0|max:30',
            'popup_auto_close' => 'required|integer|min:0|max:300',
            'popup_frequency' => 'required|in:' . implode(',', array_keys(Popup::FREQUENCIES)),
            'popup_pages' => 'required|in:' . implode(',', array_keys(Popup::PAGES)),
            'popup_devices' => 'required|in:' . implode(',', array_keys(Popup::DEVICES)),
            'popup_size' => 'required|in:' . implode(',', array_keys(Popup::SIZES)),
        ]);

        $data['popup_enabled'] = $request->boolean('popup_enabled') ? '1' : '0';
        $data['popup_backdrop_close'] = $request->boolean('popup_backdrop_close') ? '1' : '0';

        foreach ($data as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => (string) $value, 'group' => 'popup', 'label' => Popup::FIELDS[$key][0] ?? $key, 'type' => 'string', 'status' => 1]
            );
        }

        SiteSettings::flush();

        return redirect()->route('admin.popup.edit')->with('success', $data['popup_enabled'] === '1' ? 'Pop-up saved and switched ON.' : 'Pop-up saved (switched off).');
    }
}
