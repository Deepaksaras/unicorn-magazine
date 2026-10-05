@extends('layouts.admin')
@php $editing = $job->exists; @endphp
@section('title', $editing ? 'Edit opening' : 'New opening')
@section('breadcrumb')
    <a href="{{ route('admin.job-openings.index') }}">Job Openings</a> <i class="ri-arrow-right-s-line"></i> {{ $editing ? $job->title : 'New' }}
@endsection
@section('page_title', $editing ? 'Edit opening' : 'New opening')
@section('content')
<form method="POST" action="{{ $editing ? route('admin.job-openings.update', $job) : route('admin.job-openings.store') }}">
    @csrf @if($editing) @method('PUT') @endif
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="a-card"><div class="a-card-body">
                <x-admin.input name="title" label="Job title" :value="$job->title" required placeholder="Senior Business Writer" />
                <x-admin.textarea name="summary" label="Summary" :value="$job->summary" rows="3" />
                <div class="row g-3">
                    <div class="col-md-6"><x-admin.textarea name="responsibilities" label="What you'll do (one per line)" :value="implode(PHP_EOL, $job->responsibilities ?? [])" rows="6" /></div>
                    <div class="col-md-6"><x-admin.textarea name="requirements" label="What we're looking for (one per line)" :value="implode(PHP_EOL, $job->requirements ?? [])" rows="6" /></div>
                </div>
            </div></div>
        </div>
        <div class="col-lg-4">
            <div class="a-card"><div class="a-card-body">
                <x-admin.status :value="$job->status" :labels="['1' => 'Open', '0' => 'Closed']" />
                <x-admin.input name="department" label="Department" :value="$job->department" placeholder="Editorial" />
                <x-admin.input name="employment_type" label="Type" :value="$job->employment_type" placeholder="Full Time" list="jobTypes" />
                <datalist id="jobTypes"><option>Full Time</option><option>Part Time</option><option>Contract</option><option>Internship</option><option>Freelance</option></datalist>
                <x-admin.input name="location" label="Location" :value="$job->location" placeholder="Mumbai / Hybrid" />
                <x-admin.input name="apply_url" label="Apply link (optional)" :value="$job->apply_url" help="Empty = opens an email to the address set on the Career page." />
                <x-admin.input type="number" name="position" label="Order" :value="$job->position" />
            </div></div>
        </div>
    </div>
    @include('admin.partials.savebar', ['cancel' => route('admin.job-openings.index')])
</form>
@endsection
