@extends('layouts.admin')
@section('title', 'Ad Enquiries')
@section('breadcrumb', 'Ad Enquiries')
@section('page_title', 'Advertising Enquiries')
@section('page_subtitle', 'Leads from the “Advertise with Us” form. Track each one through the pipeline.')
@section('content')
<div class="a-card">
    <x-admin.date-filter :range="$range" :count="$enquiries->total()" noun="enquiries" column="by date received" />

    <x-admin.toolbar placeholder="Search name, company or email…">
        <select name="status" class="form-select" data-autosubmit>
            <option value="">All stages</option>
            @foreach($stages as $k => [$label])<option value="{{ $k }}" @selected(request('status') === $k)>{{ $label }}</option>@endforeach
        </select>
    </x-admin.toolbar>
    @if($enquiries->count())
        <div class="table-responsive">
            <table class="a-table">
                <thead><tr><th>Contact</th><th>Company</th><th>Interest</th><th>Budget</th><th>Stage</th><th>Received</th><th></th></tr></thead>
                <tbody>
                @foreach($enquiries as $e)
                    @php [$label, $tone] = $stages[$e->enquiry_status] ?? [ucfirst($e->enquiry_status), 'muted']; @endphp
                    <tr class="{{ $e->is_read ? '' : 'a-unread' }}">
                        <td class="a-title-cell"><strong>{{ $e->name }}</strong><small>{{ $e->email }}</small></td>
                        <td>{{ $e->company ?: '—' }}<div class="small text-muted">{{ $e->industry }}</div></td>
                        <td class="small">{{ \Illuminate\Support\Str::limit($e->placement_interest, 40) ?: '—' }}</td>
                        <td class="small">{{ $e->budget ?: '—' }}</td>
                        <td><span class="a-pill {{ $tone }}">{{ $label }}</span></td>
                        <td class="small text-muted text-nowrap"><span title="{{ $e->created_at->format('d M Y, H:i') }}">{{ $e->created_at->format('d M Y') }}</span><div class="small">{{ $e->created_at->format('H:i') }}</div></td>
                        <td><div class="a-row-actions">
                            <a href="{{ route('admin.advertising-enquiries.show', $e) }}" class="btn btn-soft btn-icon btn-sm" title="Open"><i class="ri-eye-line"></i></a>
                            <x-admin.delete :action="route('admin.advertising-enquiries.destroy', $e)" message="Delete this enquiry?" />
                        </div></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @include('admin.partials.pagination', ['items' => $enquiries])
    @else
        <x-admin.empty icon="ri-hand-coin-line" title="No enquiries yet" text="They arrive from the Advertise with Us page." />
    @endif
</div>
@endsection
