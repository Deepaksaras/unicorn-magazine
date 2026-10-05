@php
    $user = auth()->user();
    $siteName = \App\Support\SiteSettings::get('site_name', 'The Unicorn Magazine');
    $unreadMessages = \App\Models\ContactMessage::where('status', '!=', 4)->where('is_read', 0)->count();
    $unreadEnquiries = \App\Models\AdvertisingEnquiry::where('status', '!=', 4)->where('is_read', 0)->count();
    $role = $user?->roles()->orderBy('roles.id')->value('roles.name') ?? 'Team';

    $nav = [
        'Overview' => [
            ['admin.dashboard', 'Dashboard', 'ri-dashboard-3-line', 'admin.dashboard'],
        ],
        'Content' => [
            ['admin.articles.index', 'Articles', 'ri-article-line', 'admin.articles.*'],
            ['admin.categories.index', 'Categories', 'ri-folder-3-line', 'admin.categories.*'],
            ['admin.tags.index', 'Tags', 'ri-price-tag-3-line', 'admin.tags.*'],
            ['admin.profiles.index', 'Profiles', 'ri-user-star-line', 'admin.profiles.*'],
            ['admin.reports.index', 'Reports', 'ri-file-chart-line', 'admin.reports.*'],
            ['admin.media.index', 'Media Library', 'ri-image-2-line', 'admin.media.*'],
        ],
        'Website' => [
            ['admin.pages.index', 'Pages', 'ri-layout-4-line', 'admin.pages.*'],
            ['admin.breaking-news.index', 'Breaking News', 'ri-flashlight-line', 'admin.breaking-news.*'],
            ['admin.team-members.index', 'Team Members', 'ri-team-line', 'admin.team-members.*'],
            ['admin.job-openings.index', 'Job Openings', 'ri-briefcase-4-line', 'admin.job-openings.*'],
            ['admin.menus.index', 'Menus', 'ri-menu-search-line', 'admin.menus.*'],
            ['admin.subscribe-popup.edit', 'Subscribe Pop-up', 'ri-mail-add-line', 'admin.subscribe-popup.*'],
        ],
        'Advertising' => [
            ['admin.advertisements.index', 'Advertisements', 'ri-advertisement-line', 'admin.advertisements.*'],
            ['admin.advertisement-placements.index', 'Ad Placements', 'ri-layout-masonry-line', 'admin.advertisement-placements.*'],
            ['admin.popup.edit', 'Pop-up Ad', 'ri-window-line', 'admin.popup.*'],
        ],
        'Inbox' => [
            ['admin.contact-messages.index', 'Contact Messages', 'ri-mail-open-line', 'admin.contact-messages.*', $unreadMessages],
            ['admin.advertising-enquiries.index', 'Ad Enquiries', 'ri-hand-coin-line', 'admin.advertising-enquiries.*', $unreadEnquiries],
            ['admin.subscribers.index', 'Subscribers', 'ri-mail-star-line', 'admin.subscribers.*'],
        ],
        'System' => [
            ['admin.users.index', 'Users', 'ri-shield-user-line', 'admin.users.*'],
            ['admin.seo.edit', 'SEO', 'ri-search-eye-line', 'admin.seo.*'],
            ['admin.settings.index', 'Settings', 'ri-settings-4-line', 'admin.settings.*'],
        ],
    ];
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Dashboard') · {{ $siteName }} CMS</title>

    <link rel="shortcut icon" href="{{ \App\Support\Media::url(\App\Support\SiteSettings::get('site_favicon'), asset('img/favicon.svg')) }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&family=Merriweather:wght@700;900&display=swap" rel="stylesheet">
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/remixicon.css') }}" rel="stylesheet">
    <link href="{{ asset('cms/admin.css') }}?v=4" rel="stylesheet">
    @stack('styles')
</head>
<body class="admin-body" data-upload-url="{{ route('admin.media.upload') }}">

<!-- ================= SIDEBAR ================= -->
<aside class="a-sidebar" id="adminSidebar">
    <a href="{{ route('admin.dashboard') }}" class="a-brand">
        <span class="a-brand-mark">U</span>
        <span class="a-brand-text">
            <strong>{{ \Illuminate\Support\Str::limit($siteName, 22) }}</strong>
            <span>Content Studio</span>
        </span>
    </a>

    <nav class="a-nav">
        @foreach($nav as $group => $links)
            <div class="a-nav-label">{{ $group }}</div>
            @foreach($links as $link)
                <a href="{{ route($link[0]) }}" class="a-nav-link {{ request()->routeIs($link[3]) ? 'active' : '' }}">
                    <i class="{{ $link[2] }}"></i>
                    <span>{{ $link[1] }}</span>
                    @if(!empty($link[4]))
                        <span class="a-nav-count">{{ $link[4] }}</span>
                    @endif
                </a>
            @endforeach
        @endforeach
    </nav>

    <div class="a-sidebar-foot">
        <a href="{{ route('home') }}" target="_blank" class="a-nav-link px-2 py-2 m-0">
            <i class="ri-external-link-line"></i> <span>View website</span>
        </a>
    </div>
</aside>
<div class="a-backdrop"></div>

<!-- ================= MAIN ================= -->
<div class="a-main">

    <header class="a-topbar">
        <button type="button" class="a-icon-btn d-lg-none" data-toggle-nav aria-label="Menu">
            <i class="ri-menu-2-line"></i>
        </button>

        <div class="a-crumbs d-none d-sm-block">
            <a href="{{ route('admin.dashboard') }}"><i class="ri-home-5-line m-0"></i></a>
            @hasSection('breadcrumb')
                <i class="ri-arrow-right-s-line"></i> @yield('breadcrumb')
            @endif
        </div>

        <div class="ms-auto d-flex align-items-center gap-2">
            <a href="{{ route('admin.articles.create') }}" class="btn btn-ink btn-sm d-none d-md-inline-flex align-items-center gap-1">
                <i class="ri-add-line"></i> New article
            </a>
            <a href="{{ route('home') }}" target="_blank" class="a-icon-btn" title="View website"><i class="ri-global-line"></i></a>

            <div class="dropdown">
                <button class="a-user" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="a-avatar">{{ strtoupper(mb_substr($user->name ?? 'A', 0, 1)) }}</span>
                    <span class="d-none d-md-block">
                        <span class="a-user-name d-block">{{ $user->name ?? 'Admin' }}</span>
                        <span class="a-user-role d-block">{{ $role }}</span>
                    </span>
                    <i class="ri-arrow-down-s-line text-muted"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0 p-2" style="border-radius:12px;min-width:210px">
                    <li><a class="dropdown-item rounded-2 py-2" href="{{ route('admin.users.edit', $user) }}"><i class="ri-user-settings-line me-2"></i>My account</a></li>
                    <li><a class="dropdown-item rounded-2 py-2" href="{{ route('admin.settings.index') }}"><i class="ri-settings-4-line me-2"></i>Settings</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('admin.logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item rounded-2 py-2 text-danger"><i class="ri-logout-box-r-line me-2"></i>Sign out</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </header>

    <main class="a-content">
        @hasSection('page_title')
            <div class="a-page-head">
                <div>
                    <h1>@yield('page_title')</h1>
                    @hasSection('page_subtitle')
                        <p>@yield('page_subtitle')</p>
                    @endif
                </div>
                @hasSection('page_actions')
                    <div class="a-page-actions">@yield('page_actions')</div>
                @endif
            </div>
        @endif

        @if($errors->any() && !isset($hideErrorSummary))
            <div class="alert alert-danger border-0 shadow-sm d-flex gap-2 align-items-start" style="border-radius:12px">
                <i class="ri-error-warning-line fs-5"></i>
                <div>
                    <strong>Please fix the highlighted fields.</strong>
                    <ul class="mb-0 mt-1 ps-3 small">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        @yield('content')
    </main>
</div>

<!-- Flash toasts -->
<div class="a-toasts">
    @foreach(['success' => 'success', 'error' => 'error', 'status' => 'success'] as $key => $type)
        @if(session($key))
            <div class="a-toast {{ $type }}">
                <i class="{{ $type === 'error' ? 'ri-error-warning-line' : 'ri-checkbox-circle-line' }}"></i>
                <div>{{ session($key) }}</div>
                <button type="button" class="x" aria-label="Close">&times;</button>
            </div>
        @endif
    @endforeach
</div>

<script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
@stack('editor')
<script src="{{ asset('cms/admin.js') }}?v=6"></script>
@stack('scripts')
</body>
</html>
