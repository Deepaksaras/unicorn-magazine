@extends('layouts.front')

@section('title', $content->metaTitle())
@if($content->metaDescription())
    @section('description', $content->metaDescription())
@endif

@php
    use App\Support\SiteSettings as S;
    $c = $content;
    $socials = [
        ['linkedin_url', 'LinkedIn', 'ri-linkedin-fill'],
        ['facebook_url', 'Facebook', 'ri-facebook-fill'],
        ['instagram_url', 'Instagram', 'ri-instagram-line'],
        ['twitter_url', 'X', 'ri-twitter-x-line'],
        ['youtube_url', 'YouTube', 'ri-youtube-fill'],
    ];
@endphp

@section('content')

@include('partials.breadcrumb', ['items' => [($c->page->title ?? 'Contact') => null]])

<section class="contact-section">
    <div class="container-fluid px-4 px-lg-5">
        <div class="contact-grid">

            <!-- LEFT CONTENT -->
            <div class="contact-info">

                @if($c->enabled('intro'))
                    <h1 class="contact-title">{!! $c->lines('intro.title') !!}</h1>
                    <p class="contact-intro">{{ $c->get('intro.text') }}</p>
                @endif

                @if($c->enabled('details'))
                <div class="contact-details">

                    <div class="contact-detail">
                        <div class="contact-detail-content">
                            <h3>{{ $c->get('details.address_title') }}</h3>
                            <p>{!! $c->lines('details.address') !!}</p>
                        </div>
                    </div>

                    <div class="contact-detail">
                        <div class="contact-detail-content">
                            <h3>{{ $c->get('details.info_title') }}</h3>
                            @foreach($c->items('details.emails') as $email)
                                <a href="mailto:{{ $email }}">{{ $email }}</a>
                            @endforeach
                        </div>
                    </div>

                    <div class="contact-detail">
                        <div class="contact-detail-content">
                            <h3>{{ $c->get('details.hours_title') }}</h3>
                            <p>{!! $c->lines('details.hours') !!}</p>
                        </div>
                    </div>

                    <div class="contact-detail">
                        <div class="contact-detail-content">
                            <h3>{{ $c->get('details.social_title') }}</h3>
                            <div class="contact-socials">
                                @foreach($socials as [$key, $label, $icon])
                                    @if(S::get($key))
                                        <a href="{{ S::get($key) }}" target="_blank" rel="noopener" aria-label="{{ $label }}"><i class="{{ $icon }}"></i></a>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>

                </div>
                @endif

            </div>

            <!-- RIGHT FORM -->
            @if($c->enabled('form'))
            <div class="contact-form-wrap" id="contactForm">
                <div class="contact-form-card">

                    <div class="contact-form-heading">
                        <h2>{{ $c->get('form.heading') }}</h2>
                        <p>{{ $c->get('form.text') }}</p>
                    </div>

                    @if(session('success'))
                        <div class="cms-alert cms-alert-success"><i class="ri-checkbox-circle-line"></i><span>{{ session('success') }}</span></div>
                    @endif
                    @if($errors->any())
                        <div class="cms-alert cms-alert-error"><i class="ri-error-warning-line"></i><span>{{ $errors->first() }}</span></div>
                    @endif

                    <form method="POST" action="{{ route('contact.send') }}" novalidate>
                        @csrf

                        <div class="contact-form-row">
                            <div class="contact-field">
                                <label for="contactName">Full Name</label>
                                <input type="text" id="contactName" name="name" value="{{ old('name') }}" placeholder="Your name" autocomplete="name" required>
                            </div>
                            <div class="contact-field">
                                <label for="contactEmail">Email Address</label>
                                <input type="email" id="contactEmail" name="email" value="{{ old('email') }}" placeholder="you@example.com" autocomplete="email" required>
                            </div>
                        </div>

                        <div class="contact-form-row">
                            <div class="contact-field">
                                <label for="contactPhone">Phone Number</label>
                                <input type="tel" id="contactPhone" name="phone" value="{{ old('phone') }}" placeholder="+91 00000 00000" autocomplete="tel">
                            </div>
                            <div class="contact-field">
                                <label for="contactSubject">Subject</label>
                                <select id="contactSubject" name="subject" required>
                                    <option value="" disabled {{ old('subject') ? '' : 'selected' }}>Select a subject</option>
                                    @foreach($c->items('form.subjects') as $subject)
                                        <option value="{{ $subject }}" {{ old('subject') === $subject ? 'selected' : '' }}>{{ $subject }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="contact-field">
                            <label for="contactMessage">Your Message</label>
                            <textarea id="contactMessage" name="message" rows="6" placeholder="Write your message here..." required>{{ old('message') }}</textarea>
                        </div>

                        <div class="contact-form-bottom">
                            <label class="contact-checkbox">
                                <input type="checkbox" name="consent" value="1" {{ old('consent') ? 'checked' : '' }} required>
                                <span>{{ $c->get('form.consent') }}</span>
                            </label>
                            <button type="submit" class="contact-submit-btn">
                                {{ $c->get('form.button') }}
                                <i class="ri-arrow-right-line"></i>
                            </button>
                        </div>
                    </form>

                </div>
            </div>
            @endif

        </div>
    </div>
</section>

@endsection
