<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Sign in · {{ \App\Support\SiteSettings::get('site_name', 'The Unicorn Magazine') }} CMS</title>
    <link rel="shortcut icon" href="{{ asset('img/favicon.svg') }}">
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&family=Merriweather:wght@700;900&display=swap" rel="stylesheet">
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/remixicon.css') }}" rel="stylesheet">
    <link href="{{ asset('cms/admin.css') }}?v=1" rel="stylesheet">
</head>
<body class="admin-body">
<div class="a-auth">
    <div class="a-auth-art">
        <div class="d-flex align-items-center gap-3">
            <span class="a-brand-mark">U</span>
            <span class="a-brand-text"><strong>{{ \App\Support\SiteSettings::get('site_name', 'The Unicorn Magazine') }}</strong><span>Content Studio</span></span>
        </div>
        <div>
            <h2>Stories behind the ideas shaping tomorrow.</h2>
            <p>Write, schedule and publish — every page of the magazine is managed right here.</p>
        </div>
        <div class="small" style="color:rgba(255,255,255,.4)">&copy; {{ date('Y') }}</div>
        <span class="ring"></span><span class="ring two"></span>
    </div>

    <div class="a-auth-form">
        <div class="a-auth-box">
            <h1>Welcome back</h1>
            <p class="text-muted mb-4">Sign in to continue to the CMS.</p>

            @if(session('status'))
                <div class="alert alert-success border-0 small" style="border-radius:10px">{{ session('status') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger border-0 small d-flex gap-2" style="border-radius:10px"><i class="ri-error-warning-line"></i>{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('admin.login') }}">
                @csrf
                <div class="a-field">
                    <label class="form-label" for="email">Email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ri-mail-line"></i></span>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" class="form-control" required autofocus autocomplete="username">
                    </div>
                </div>
                <div class="a-field">
                    <div class="d-flex justify-content-between">
                        <label class="form-label" for="password">Password</label>
                        @if(Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="small a-link-muted">Forgot?</a>
                        @endif
                    </div>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ri-lock-2-line"></i></span>
                        <input type="password" name="password" id="password" class="form-control" required autocomplete="current-password">
                        <button type="button" class="btn btn-soft" onclick="var p=document.getElementById('password');p.type=p.type==='password'?'text':'password';this.firstElementChild.className=p.type==='password'?'ri-eye-line':'ri-eye-off-line'"><i class="ri-eye-line"></i></button>
                    </div>
                </div>
                <div class="a-field">
                    <label class="form-check">
                        <input type="checkbox" name="remember" value="1" class="form-check-input"> <span class="form-check-label small">Keep me signed in</span>
                    </label>
                </div>
                <button type="submit" class="btn btn-ink w-100 py-2"><i class="ri-login-box-line me-1"></i> Sign in</button>
            </form>

            <a href="{{ route('home') }}" class="d-inline-block mt-4 small a-link-muted"><i class="ri-arrow-left-line"></i> Back to website</a>
        </div>
    </div>
</div>
</body>
</html>
