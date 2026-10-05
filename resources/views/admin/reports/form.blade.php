@extends('layouts.admin')
@php $editing = $report->exists; @endphp
@section('title', $editing ? 'Edit report' : 'New report')
@section('breadcrumb')
    <a href="{{ route('admin.reports.index') }}">Reports</a> <i class="ri-arrow-right-s-line"></i> {{ $editing ? 'Edit' : 'New' }}
@endsection
@section('page_title', $editing ? 'Edit report' : 'New report')
@if($editing && $report->status == 1)
    @section('page_actions')
        <a href="{{ route('report', $report->slug) }}" target="_blank" class="btn btn-soft"><i class="ri-external-link-line me-1"></i> View on site</a>
    @endsection
@endif
@include('admin.partials.editor')
@section('content')
<form method="POST" enctype="multipart/form-data" action="{{ $editing ? route('admin.reports.update', $report) : route('admin.reports.store') }}">
    @csrf @if($editing) @method('PUT') @endif
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="a-card"><div class="a-card-body">
                <x-admin.input name="title" label="Title" :value="$report->title" required />
                <x-admin.textarea name="description" label="Short description" :value="$report->description" rows="3" help="Shown on report cards and at the top of the report page." />
                <div class="row g-3">
                    <div class="col-md-4"><x-admin.select name="report_type" label="Type" :value="$report->report_type" :options="$types" /></div>
                    <div class="col-md-4"><x-admin.input name="category_label" label="Card label" :value="$report->category_label" placeholder="Economy" /></div>
                    <div class="col-md-4"><x-admin.input name="author_name" label="By" :value="$report->author_name" placeholder="Ananya Sharma" /></div>
                </div>
            </div></div>
            <div class="a-card">
                <div class="a-card-head"><div><h3>Report page</h3><div class="a-card-sub">Full text shown on {{ url('/report') }}/{{ $report->slug ?: 'title' }} – key findings, charts, methodology…</div></div></div>
                <div class="a-card-body">
                    <x-admin.textarea name="content" label="Page content" :value="$report->content" rich data-height="460" />
                </div>
            </div>
            <div class="a-card">
                <div class="a-card-head"><div><h3>Download</h3><div class="a-card-sub">Upload a file, or link to an external page.</div></div></div>
                <div class="a-card-body">
                    @if($report->file_path)
                        <div class="a-help mb-3"><i class="ri-file-3-line"></i>
                            <div>Current file: <a href="{{ asset('storage/' . $report->file_path) }}" target="_blank">{{ basename($report->file_path) }}</a>
                                ({{ number_format(($report->file_size ?? 0) / 1024, 0) }} KB)
                                <label class="ms-2"><input type="checkbox" name="report_file_remove" value="1" class="form-check-input"> Remove</label></div>
                        </div>
                    @endif
                    <x-admin.input type="file" name="report_file" label="File (PDF, DOCX, XLSX, PPTX, ZIP — max 30 MB)" />
                    <x-admin.input name="external_url" label="…or external link" :value="$report->external_url" placeholder="https://" />
                </div>
            </div>
            <x-admin.seo :model="$report" :title="$report->title" :description="$report->description" :url="$report->exists && $report->slug ? route('report', $report->slug) : null" image-help="Shown when the report is shared. Empty = the cover image." />
        </div>
        <div class="col-lg-4">
            <x-admin.short-link :item="$report" />
            <div class="a-card"><div class="a-card-body">
                <x-admin.status :value="$report->status" />
                <x-admin.input type="date" name="report_date" label="Report date" :value="optional($report->report_date)->format('Y-m-d')" />
                <x-admin.image name="cover_image" label="Cover image" :value="$report->cover_image" />
                <x-admin.toggle name="is_exclusive" label="Exclusive (home sidebar card)" :checked="$report->is_exclusive" />
                <x-admin.toggle name="is_premium" label="Premium" :checked="$report->is_premium" />
                <x-admin.input type="number" step="0.01" name="price" label="Price" :value="$report->price" />
            </div></div>
        </div>
    </div>
    @include('admin.partials.savebar', ['cancel' => route('admin.reports.index')])
</form>
@endsection
