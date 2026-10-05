@extends('layouts.admin')
@section('title', 'Pop-up Ad')
@section('breadcrumb', 'Pop-up Ad')
@section('page_title', 'Pop-up Ad')
@section('page_subtitle', 'An advertisement that opens in a window on the website. Choose when it appears, how often, and when it closes.')
@section('page_actions')
    <a href="{{ route('home', ['popup_preview' => 1]) }}" target="_blank" class="btn btn-soft"><i class="ri-eye-line me-1"></i> Preview on website</a>
    <a href="{{ route('admin.advertisements.create', ['placement' => \App\Support\Popup::PLACEMENT]) }}" class="btn btn-ink"><i class="ri-add-line me-1"></i> New pop-up ad</a>
@endsection

@section('content')
@php
    use App\Support\Popup;
    $s = $settings;
    $live = $ads->filter(fn ($ad) => $ad->status == 1 && $ad->is_active
        && (!$ad->start_date || $ad->start_date->lte(today()))
        && (!$ad->end_date || $ad->end_date->gte(today())));
@endphp

@if($s['popup_enabled'] === '1' && $live->isEmpty())
    <div class="alert alert-warning border-0 shadow-sm" style="border-radius:12px">
        <i class="ri-error-warning-line me-1"></i> The pop-up is switched on, but there is no live pop-up ad yet, so nothing will show. Add one with <strong>New pop-up ad</strong>.
    </div>
@endif

<form method="POST" action="{{ route('admin.popup.update') }}">
    @csrf @method('PUT')
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="a-card">
                <div class="a-card-head"><div><h3>Show the pop-up</h3><div class="a-card-sub">Switch it off any time without deleting the ad</div></div></div>
                <div class="a-card-body">
                    <x-admin.toggle name="popup_enabled" label="Pop-up is ON" :checked="$s['popup_enabled'] === '1'" help="When off, no pop-up appears anywhere on the website." />
                </div>
            </div>

            <div class="a-card">
                <div class="a-card-head"><div><h3>Timing</h3><div class="a-card-sub">All times in seconds</div></div></div>
                <div class="a-card-body">
                    <div class="row g-3">
                        <div class="col-md-6"><x-admin.input type="number" min="0" max="120" name="popup_delay" label="Show after" :value="$s['popup_delay']" help="Seconds after the page opens (0 = at once)." /></div>
                        <div class="col-md-6"><x-admin.input type="number" min="0" max="100" name="popup_scroll" label="…and after scrolling (%)" :value="$s['popup_scroll']" help="0 = off. E.g. 50 = only when the reader is halfway down the page (kinder on phones)." /></div>
                        <div class="col-md-6"><x-admin.input type="number" min="0" max="30" name="popup_close_after" label="Close button appears after" :value="$s['popup_close_after']" help="0 = immediately. Keep it 3 or less." /></div>
                        <div class="col-md-6"><x-admin.input type="number" min="0" max="300" name="popup_auto_close" label="Close automatically after" :value="$s['popup_auto_close']" help="0 = stays open until the visitor closes it." /></div>
                    </div>
                </div>
            </div>

            <div class="a-card">
                <div class="a-card-head"><h3>Who sees it</h3></div>
                <div class="a-card-body">
                    <div class="row g-3">
                        <div class="col-md-6"><x-admin.select name="popup_frequency" label="How often per visitor" :value="$s['popup_frequency']" :options="Popup::FREQUENCIES" help="“Once per day” is recommended." /></div>
                        <div class="col-md-6"><x-admin.select name="popup_pages" label="Show on" :value="$s['popup_pages']" :options="Popup::PAGES" /></div>
                        <div class="col-md-6"><x-admin.select name="popup_devices" label="Devices" :value="$s['popup_devices']" :options="Popup::DEVICES" /></div>
                        <div class="col-md-6"><x-admin.select name="popup_size" label="Size" :value="$s['popup_size']" :options="Popup::SIZES" help="On phones it always fits the screen." /></div>
                    </div>
                    <x-admin.toggle name="popup_backdrop_close" label="Close by clicking outside or pressing Esc" :checked="$s['popup_backdrop_close'] === '1'" help="Only works once the close button is showing." />
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="a-card">
                <div class="a-card-head">
                    <div><h3>Pop-up ads</h3><div class="a-card-sub">Normal ads in the “Pop-up Ad” placement. If several are live, one is picked at random.</div></div>
                </div>
                @if($ads->count())
                    <div class="table-responsive">
                        <table class="a-table">
                            <thead><tr><th>Ad</th><th class="text-end">Views</th><th class="text-end">Clicks</th><th></th></tr></thead>
                            <tbody>
                                @foreach($ads as $ad)
                                    <tr>
                                        <td class="a-title-cell">
                                            <strong>{{ $ad->title }}</strong>
                                            <small>
                                                @if($live->contains('id', $ad->id))<span class="a-pill success py-0">Live</span>@else<span class="a-pill muted py-0">Off</span>@endif
                                                {{ $ad->code ? 'Code' : ($ad->image ? 'Image' : 'Text') }}
                                            </small>
                                        </td>
                                        <td class="text-end">{{ number_format($ad->impression_count) }}</td>
                                        <td class="text-end fw-semibold">{{ number_format($ad->click_count) }}</td>
                                        <td><div class="a-row-actions">
                                            <a href="{{ route('admin.advertisements.report', $ad) }}" class="btn btn-soft btn-icon btn-sm" title="Report"><i class="ri-bar-chart-2-line"></i></a>
                                            <x-admin.edit-link :href="route('admin.advertisements.edit', $ad)" />
                                        </div></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <x-admin.empty icon="ri-window-line" title="No pop-up ad yet" text="Create an ad and choose the “Pop-up Ad” placement." :action="route('admin.advertisements.create', ['placement' => Popup::PLACEMENT])" actionLabel="New pop-up ad" />
                @endif
            </div>

            <div class="a-card">
                <div class="a-card-body small text-muted">
                    <p class="mb-2"><i class="ri-lightbulb-line me-1 text-warning"></i><strong class="text-body">Good to know</strong></p>
                    <ul class="ps-3 mb-0">
                        <li>A view is counted only when the pop-up really opens; clicks are counted like other ads.</li>
                        <li>“Preview on website” shows the pop-up right away to you (logged in), even when it is switched off.</li>
                        <li>Image size for the pop-up: about 1200 × 1000 px.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    @include('admin.partials.savebar', ['cancel' => route('admin.dashboard'), 'label' => 'Save pop-up settings'])
</form>
@endsection
