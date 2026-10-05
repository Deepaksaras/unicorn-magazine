@extends('layouts.admin')
@section('title', 'Enquiry from ' . $enquiry->name)
@section('breadcrumb')
    <a href="{{ route('admin.advertising-enquiries.index') }}">Ad Enquiries</a> <i class="ri-arrow-right-s-line"></i> {{ $enquiry->company ?: $enquiry->name }}
@endsection
@section('page_title', ($enquiry->company ?: $enquiry->name))
@section('page_subtitle', 'Received ' . $enquiry->created_at->format('M d, Y \a\t H:i'))
@section('page_actions')
    <a href="mailto:{{ $enquiry->email }}?subject={{ rawurlencode('Your advertising enquiry') }}" class="btn btn-ink"><i class="ri-reply-line me-1"></i> Reply by email</a>
    <x-admin.delete :action="route('admin.advertising-enquiries.destroy', $enquiry)" label="Delete" size="md" message="Delete this enquiry?" />
@endsection
@section('content')
<div class="row g-4">
    <div class="col-lg-8">
        <div class="a-card">
            <div class="a-card-head"><h3>Campaign brief</h3></div>
            <div class="a-card-body"><div class="a-message-body">{{ $enquiry->message }}</div></div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="a-card">
            <div class="a-card-head"><h3>Pipeline stage</h3></div>
            <div class="a-card-body">
                <form method="POST" action="{{ route('admin.advertising-enquiries.update', $enquiry) }}" class="d-flex gap-2">
                    @csrf @method('PUT')
                    <select name="enquiry_status" class="form-select">
                        @foreach($stages as $k => [$label])<option value="{{ $k }}" @selected($enquiry->enquiry_status === $k)>{{ $label }}</option>@endforeach
                    </select>
                    <button class="btn btn-ink">Save</button>
                </form>
            </div>
        </div>
        <div class="a-card"><div class="a-card-body">
            <dl class="a-dl">
                <dt>Name</dt><dd>{{ $enquiry->name }}</dd>
                <dt>Email</dt><dd><a href="mailto:{{ $enquiry->email }}">{{ $enquiry->email }}</a></dd>
                <dt>Phone</dt><dd>{{ $enquiry->phone ?: '—' }}</dd>
                <dt>Company</dt><dd>{{ $enquiry->company ?: '—' }}</dd>
                <dt>Industry</dt><dd>{{ $enquiry->industry ?: '—' }}</dd>
                <dt>Budget</dt><dd>{{ $enquiry->budget ?: '—' }}</dd>
                <dt>Interested in</dt><dd>{{ $enquiry->placement_interest ?: '—' }}</dd>
            </dl>
        </div></div>
    </div>
</div>
@endsection
