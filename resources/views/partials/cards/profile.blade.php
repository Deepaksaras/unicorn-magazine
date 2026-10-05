{{-- Profile card (Home "Stories & Profiles", /profiles, "More profiles") --}}
<a href="{{ $profile->link }}" class="profile-card">
    <div class="profile-image">
        <img src="{{ $profile->image_url }}" alt="{{ $profile->display_name }}" loading="lazy">
    </div>
    <div class="profile-content">
        <span class="profile-label">{{ $profile->type_label }}</span>
        <h3 class="profile-name">{{ $profile->display_name }}</h3>
        <p class="profile-description">{{ $profile->summary ?: \Illuminate\Support\Str::limit(strip_tags($profile->biography), 110) }}</p>
        <span class="profile-arrow"><i class="ri-arrow-right-line"></i></span>
    </div>
</a>
