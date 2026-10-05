<?php

namespace App\Http\Controllers\Admin;

use App\Support\DateRange;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\Request;

class ContactMessageController extends Controller
{
    public function index(Request $request)
    {
        $range = DateRange::fromRequest($request); // Today / This month / Date range (by date received)

        $query = ContactMessage::where('status', '!=', 4)
            ->when($request->q, fn ($q, $t) => $q->where(fn ($w) => $w->where('name', 'like', "%{$t}%")->orWhere('email', 'like', "%{$t}%")->orWhere('message', 'like', "%{$t}%")))
            ->when($request->filter === 'unread', fn ($q) => $q->where('is_read', 0));
        $range->apply($query, 'created_at');

        $messages = $query->latest()->paginate(20)->withQueryString();

        return view('admin.contact-messages.index', compact('messages', 'range'));
    }

    public function show(ContactMessage $contactMessage)
    {
        if (!$contactMessage->is_read) {
            $contactMessage->update(['is_read' => 1]);
        }

        return view('admin.contact-messages.show', ['message' => $contactMessage]);
    }

    public function toggleRead(ContactMessage $contactMessage)
    {
        $contactMessage->update(['is_read' => $contactMessage->is_read ? 0 : 1]);

        return back()->with('success', $contactMessage->is_read ? 'Marked as read.' : 'Marked as unread.');
    }

    public function destroy(ContactMessage $contactMessage)
    {
        $contactMessage->update(['status' => 4]);

        return redirect()->route('admin.contact-messages.index')->with('success', 'Message deleted.');
    }
}
