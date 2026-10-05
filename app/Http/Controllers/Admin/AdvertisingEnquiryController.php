<?php

namespace App\Http\Controllers\Admin;

use App\Support\DateRange;

use App\Http\Controllers\Controller;
use App\Models\AdvertisingEnquiry;
use Illuminate\Http\Request;

class AdvertisingEnquiryController extends Controller
{
    public const STAGES = [
        'new' => ['New', 'info'],
        'contacted' => ['Contacted', 'warning'],
        'converted' => ['Converted', 'success'],
        'rejected' => ['Closed', 'muted'],
    ];

    public function index(Request $request)
    {
        $range = DateRange::fromRequest($request); // Today / This month / Date range (by date received)

        $query = AdvertisingEnquiry::where('status', '!=', 4)
            ->when($request->q, fn ($q, $t) => $q->where(fn ($w) => $w->where('name', 'like', "%{$t}%")->orWhere('company', 'like', "%{$t}%")->orWhere('email', 'like', "%{$t}%")))
            ->when($request->status, fn ($q, $s) => $q->where('enquiry_status', $s));
        $range->apply($query, 'created_at');

        $enquiries = $query->latest()->paginate(20)->withQueryString();

        return view('admin.advertising-enquiries.index', ['enquiries' => $enquiries, 'stages' => self::STAGES, 'range' => $range]);
    }

    public function show(AdvertisingEnquiry $advertisingEnquiry)
    {
        if (!$advertisingEnquiry->is_read) {
            $advertisingEnquiry->update(['is_read' => 1]);
        }

        return view('admin.advertising-enquiries.show', ['enquiry' => $advertisingEnquiry, 'stages' => self::STAGES]);
    }

    public function update(Request $request, AdvertisingEnquiry $advertisingEnquiry)
    {
        $data = $request->validate(['enquiry_status' => 'required|in:' . implode(',', array_keys(self::STAGES))]);
        $advertisingEnquiry->update($data);

        return back()->with('success', 'Enquiry status updated.');
    }

    public function destroy(AdvertisingEnquiry $advertisingEnquiry)
    {
        $advertisingEnquiry->update(['status' => 4]);

        return redirect()->route('admin.advertising-enquiries.index')->with('success', 'Enquiry deleted.');
    }
}
