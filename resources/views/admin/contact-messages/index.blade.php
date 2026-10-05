@extends('layouts.admin')
@section('title', 'Contact Messages')
@section('breadcrumb', 'Contact Messages')
@section('page_title', 'Contact Messages')
@section('page_subtitle', 'Submissions from the Contact page form.')
@section('content')
<div class="a-card">
    <x-admin.date-filter :range="$range" :count="$messages->total()" noun="messages" column="by date received" />

    <x-admin.toolbar placeholder="Search name, email or text…">
        <select name="filter" class="form-select" data-autosubmit>
            <option value="">All messages</option>
            <option value="unread" @selected(request('filter') === 'unread')>Unread only</option>
        </select>
    </x-admin.toolbar>
    @if($messages->count())
        <div class="table-responsive">
            <table class="a-table">
                <thead><tr><th>From</th><th>Subject</th><th>Message</th><th>Received</th><th></th></tr></thead>
                <tbody>
                @foreach($messages as $m)
                    <tr class="{{ $m->is_read ? '' : 'a-unread' }}">
                        <td class="a-title-cell"><strong>{{ $m->name }}</strong><small>{{ $m->email }}</small></td>
                        <td><span class="a-pill gold">{{ $m->subject ?: 'General' }}</span></td>
                        <td class="small text-muted"><a href="{{ route('admin.contact-messages.show', $m) }}" class="text-reset text-decoration-none">{{ \Illuminate\Support\Str::limit($m->message, 80) }}</a></td>
                        <td class="small text-nowrap text-muted"><span title="{{ $m->created_at->format('d M Y, H:i') }}">{{ $m->created_at->format('d M Y') }}</span><div class="small">{{ $m->created_at->format('H:i') }}</div></td>
                        <td><div class="a-row-actions">
                            <a href="{{ route('admin.contact-messages.show', $m) }}" class="btn btn-soft btn-icon btn-sm" title="Open"><i class="ri-eye-line"></i></a>
                            <x-admin.delete :action="route('admin.contact-messages.destroy', $m)" message="Delete this message?" />
                        </div></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @include('admin.partials.pagination', ['items' => $messages])
    @else
        <x-admin.empty icon="ri-mail-open-line" title="Inbox zero" text="New messages from the Contact page appear here." />
    @endif
</div>
@endsection
