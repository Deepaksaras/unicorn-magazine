<?php

namespace App\Models;

use App\Models\Traits\HasShortLink;
use App\Models\Traits\HasSeo;
use App\Models\Traits\HasStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Profile extends Model
{
    use HasFactory, HasStatus, HasSeo, HasShortLink;

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'label',
        'summary',
        'post_id',
        'position',
        'profile_type',
        'company_name',
        'designation',
        'industry',
        'net_worth',
        'currency',
        'biography',
        'website',
        'linkedin_url',
        'twitter_url',
        'profile_image',
        'social_links',
        'achievements',
        'view_count',
        'status',
    ];

    protected $casts = [
        'status' => 'integer',
        'position' => 'integer',
        'view_count' => 'integer',
        'social_links' => 'array',
        'achievements' => 'array',
    ];

    /**
     * User that owns this profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->name ?: ($this->user->name ?? 'Unnamed profile');
    }

    public function getImageUrlAttribute(): string
    {
        return \App\Support\Media::url($this->profile_image, \App\Support\Media::avatar($this->display_name));
    }

    /** Public profile page: /profile/{slug} */
    public function getLinkAttribute(): string
    {
        return $this->slug ? route('profile', $this->slug) : route('profiles.index');
    }

    /** Label shown on cards, e.g. "Founder" */
    public function getTypeLabelAttribute(): string
    {
        return $this->label ?: \Illuminate\Support\Str::headline((string) $this->profile_type);
    }

    /** "Chairman, Tata Sons" */
    public function getRoleLineAttribute(): string
    {
        return collect([$this->designation, $this->company_name])->filter()->implode(', ');
    }

    /**
     * Net worth as shown on the website.
     * The column is plain text, so the admin can type it exactly as it should appear:
     *   "₹3,800 Cr",  "$1.2 Billion",  "Rs 500 Crore+",  "Undisclosed"
     * A plain number (older profiles, e.g. 38000000000) is still formatted automatically
     * with the profile's currency: ₹3,800 Cr / $1.2 B.
     */
    public function getNetWorthTextAttribute(): ?string
    {
        $raw = trim((string) $this->net_worth);

        if ($raw === '') {
            return null;
        }

        // Typed as text → show exactly as typed
        $plain = str_replace([',', ' '], '', $raw);
        if (!is_numeric($plain)) {
            return $raw;
        }

        $value = (float) $plain;
        if ($value <= 0) {
            return null;
        }

        $currency = strtoupper((string) $this->currency);
        $symbol = ['INR' => '₹', 'USD' => '$', 'EUR' => '€', 'GBP' => '£'][$currency] ?? ($currency !== '' ? $currency . ' ' : '');

        if ($currency === 'INR') {
            return $value >= 1e7 ? $symbol . number_format($value / 1e7, $value >= 1e9 ? 0 : 1) . ' Cr' : $symbol . number_format($value);
        }

        return $value >= 1e9 ? $symbol . number_format($value / 1e9, 1) . ' B' : ($value >= 1e6 ? $symbol . number_format($value / 1e6, 1) . ' M' : $symbol . number_format($value));
    }

    /** Achievements as a clean list (stored as JSON array, typed one per line in the admin). */
    public function getAchievementListAttribute(): array
    {
        return collect((array) $this->achievements)->map(fn ($a) => trim((string) $a))->filter()->values()->all();
    }

    /** Biography as HTML (older profiles were saved as plain text). */
    public function getBiographyHtmlAttribute(): string
    {
        $bio = (string) $this->biography;

        return $bio !== strip_tags($bio) ? $bio : nl2br(e($bio));
    }
}
