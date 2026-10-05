@extends('layouts.front')

@section('title', 'Reports - ' . \App\Support\SiteSettings::get('site_name', 'The Unicorn Magazine'))

@section('content')

@include('partials.breadcrumb', ['items' => ['Reports' => null]])

<section class="reports-section">
    <div class="container-fluid px-4 px-lg-5">

        @include('partials.section-header', ['title' => 'Reports', 'bigText' => 'REPORTS'])

        @if(session('success'))
            <div class="cms-alert cms-alert-success"><i class="ri-information-line"></i><span>{{ session('success') }}</span></div>
        @endif

        <div class="row g-5 align-items-start">
            <div class="col-lg-9">
                <div class="reports-list">
                    @forelse($reports as $report)
                        @include('partials.cards.report', ['report' => $report])
                    @empty
                        <div class="cms-empty">No reports have been published yet.</div>
                    @endforelse
                </div>

                @if($reports->hasPages())
                    <div class="cms-pagination my-4">{{ $reports->links() }}</div>
                @endif
            </div>

            <div class="col-lg-3">
                @include('partials.sidebar-featured', ['posts' => $featured, 'title' => 'Featured'])
            </div>
        </div>

    </div>
</section>

@endsection
