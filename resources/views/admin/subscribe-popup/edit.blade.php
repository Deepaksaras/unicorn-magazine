@extends('layouts.admin')
@section('title', 'Subscribe Pop-up')
@section('breadcrumb', 'Subscribe Pop-up')
@section('page_title', 'Subscribe Pop-up')
@section('page_subtitle', 'The newsletter window. It never shows to people who are already subscribed, and never on top of the ad pop-up.')
@section('page_actions')
    <a href="{{ route('admin.subscribers.index') }}" class="btn btn-soft"><i class="ri-mail-star-line me-1"></i> Subscribers</a>
    <a href="{{ route('admin.settings.index', ['tab' => 'subscribe']) }}" class="btn btn-soft"><i class="ri-text me-1"></i> Edit pop-up text</a>
@endsection

@section('content')
@php
    use App\Support\Subscription;
    $s = $settings;
    $googleReady = Subscription::providerReady('google');
    $linkedinReady = Subscription::providerReady('linkedin');
@endphp

<form method="POST" action="{{ route('admin.subscribe-popup.update') }}" autocomplete="off">
    @csrf @method('PUT')
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="a-card">
                <div class="a-card-head"><div><h3>Automatic pop-up</h3><div class="a-card-sub">The “Subscribe” button in the header always works, even when this is off</div></div></div>
                <div class="a-card-body">
                    <x-admin.toggle name="subpop_enabled" label="Show the subscribe pop-up automatically" :checked="$s['subpop_enabled'] === '1'" help="When off, the pop-up only opens when a visitor clicks Subscribe." />
                    <div class="row g-3">
                        <div class="col-md-6"><x-admin.input type="number" min="0" max="600" name="subpop_delay" label="Show after (seconds)" :value="$s['subpop_delay']" help="Seconds after the page opens. If the ad pop-up is showing, it waits until the ad is closed." /></div>
                        <div class="col-md-6"><x-admin.select name="subpop_interval" label="If closed without subscribing, show again after" :value="$s['subpop_interval']" :options="Subscription::INTERVALS" help="Counts from the moment the visitor closes it." /></div>
                    </div>
                </div>
            </div>

            <div class="a-card">
                <div class="a-card-head">
                    <div><h3><i class="ri-google-fill me-1"></i>Continue with Google</h3><div class="a-card-sub">Optional. Lets visitors subscribe with their Google account in one click.</div></div>
                    @if($googleReady)<span class="a-pill success">Connected</span>@else<span class="a-pill muted">Not set up</span>@endif
                </div>
                <div class="a-card-body">
                    <x-admin.input name="google_client_id" label="Client ID" :value="$s['google_client_id']" placeholder="1234567890-abc.apps.googleusercontent.com" />
                    <x-admin.input type="password" name="google_client_secret" label="Client secret" value="" :placeholder="$s['google_client_secret'] ? 'Saved — leave empty to keep it' : 'Paste the client secret'" autocomplete="new-password" />
                    <div class="a-help">
                        <i class="ri-information-line"></i>
                        <div>
                            Google Cloud Console → APIs &amp; Services → Credentials → Create OAuth client ID (Web application).<br>
                            Authorised redirect URI:
                            <code>{{ route('subscribe.oauth.callback', 'google') }}</code>
                            <button type="button" class="btn btn-soft btn-sm ms-1" data-copy="{{ route('subscribe.oauth.callback', 'google') }}">Copy</button>
                        </div>
                    </div>
                    @if($s['google_client_id'])<label class="small mt-2 d-block"><input type="checkbox" name="google_remove" value="1" class="form-check-input me-1"> Remove Google sign-up</label>@endif
                </div>
            </div>

            <div class="a-card">
                <div class="a-card-head">
                    <div><h3><i class="ri-linkedin-fill me-1"></i>Continue with LinkedIn</h3><div class="a-card-sub">Optional. Uses “Sign In with LinkedIn using OpenID Connect”.</div></div>
                    @if($linkedinReady)<span class="a-pill success">Connected</span>@else<span class="a-pill muted">Not set up</span>@endif
                </div>
                <div class="a-card-body">
                    <x-admin.input name="linkedin_client_id" label="Client ID" :value="$s['linkedin_client_id']" />
                    <x-admin.input type="password" name="linkedin_client_secret" label="Client secret" value="" :placeholder="$s['linkedin_client_secret'] ? 'Saved — leave empty to keep it' : 'Paste the client secret'" autocomplete="new-password" />
                    <div class="a-help">
                        <i class="ri-information-line"></i>
                        <div>
                            LinkedIn Developers → your app → Products → add “Sign In with LinkedIn using OpenID Connect” → Auth tab.<br>
                            Authorised redirect URL:
                            <code>{{ route('subscribe.oauth.callback', 'linkedin') }}</code>
                            <button type="button" class="btn btn-soft btn-sm ms-1" data-copy="{{ route('subscribe.oauth.callback', 'linkedin') }}">Copy</button>
                        </div>
                    </div>
                    @if($s['linkedin_client_id'])<label class="small mt-2 d-block"><input type="checkbox" name="linkedin_remove" value="1" class="form-check-input me-1"> Remove LinkedIn sign-up</label>@endif
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="a-card">
                <div class="a-card-head"><div><h3>Active subscribers</h3><div class="a-card-sub">How they subscribed</div></div><strong class="fs-4">{{ number_format($total) }}</strong></div>
                <div class="a-card-body">
                    @forelse($sources as $source => $count)
                        <div class="d-flex justify-content-between py-2 {{ $loop->last ? '' : 'border-bottom' }}" style="border-color:var(--line-2)!important">
                            <span><i class="{{ ['google' => 'ri-google-fill', 'linkedin' => 'ri-linkedin-fill', 'modal' => 'ri-window-line', 'footer' => 'ri-layout-bottom-line'][$source] ?? 'ri-mail-line' }} me-2 text-muted"></i>{{ ['modal' => 'Pop-up form', 'footer' => 'Footer form', 'about' => 'About page', 'website' => 'Website form', 'google' => 'Google', 'linkedin' => 'LinkedIn'][$source] ?? \Illuminate\Support\Str::headline($source) }}</span>
                            <strong>{{ number_format($count) }}</strong>
                        </div>
                    @empty
                        <div class="text-muted small">No subscribers yet.</div>
                    @endforelse
                </div>
            </div>

            <div class="a-card">
                <div class="a-card-body small text-muted">
                    <p class="mb-2"><i class="ri-lightbulb-line me-1 text-warning"></i><strong class="text-body">How it decides</strong></p>
                    <ol class="ps-3 mb-0">
                        <li>Is the <strong>ad pop-up</strong> showing or about to show? → the ad goes first, this one waits until it is closed.</li>
                        <li>Is the visitor <strong>already subscribed</strong> (e-mail form, Google, LinkedIn, or an existing subscriber record)? → never shown.</li>
                        <li>Did they <strong>close it</strong> recently? → shown again only after the time you chose above.</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    @include('admin.partials.savebar', ['cancel' => route('admin.dashboard'), 'label' => 'Save settings'])
</form>
@endsection
