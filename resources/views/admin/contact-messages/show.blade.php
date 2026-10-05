@extends('layouts.admin')
@section('title', 'Message from ' . $message->name)
@section('breadcrumb')
    <a href="{{ route('admin.contact-messages.index') }}">Contact Messages</a> <i class="ri-arrow-right-s-line"></i> {{ $message->name }}
@endsection
@section('page_title', $message->subject ?: 'Message')
@section('page_subtitle', 'Received ' . $message->created_at->format('M d, Y \a\t H:i'))
@section('page_actions')
    <a href="mailto:{{ $message->email }}?subject={{ rawurlencode('Re: ' . ($message->subject ?: 'Your message')) }}" class="btn btn-ink"><i class="ri-reply-line me-1"></i> Reply by email</a>
    <form method="POST" action="{{ route('admin.contact-messages.toggle', $message) }}">@csrf @method('PATCH')
        <button class="btn btn-soft">{{ $message->is_read ? 'Mark unread' : 'Mark read' }}</button></form>
    <x-admin.delete :action="route('admin.contact-messages.destroy', $message)" label="Delete" size="md" message="Delete this message?" />
@endsection
@section('content')
<div class="row g-4">
    <div class="col-lg-8"><div class="a-card"><div class="a-card-body"><div class="a-message-body">{{ $message->message }}</div></div></div></div>
    <div class="col-lg-4"><div class="a-card"><div class="a-card-body">
        <dl class="a-dl">
            <dt>Name</dt><dd>{{ $message->name }}</dd>
            <dt>Email</dt><dd><a href="mailto:{{ $message->email }}">{{ $message->email }}</a></dd>
            <dt>Phone</dt><dd>{{ $message->phone ?: '—' }}</dd>
            <dt>Subject</dt><dd>{{ $message->subject ?: '—' }}</dd>
            <dt>IP</dt><dd class="small text-muted">{{ $message->ip_address ?: '—' }}</dd>
        </dl>
    </div></div></div>
</div>
@endsection
