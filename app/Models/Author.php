<?php

namespace App\Models;

use App\Models\Traits\HasStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Author extends Model
{
    use HasFactory, HasStatus;

    protected $fillable = [
        'user_id',
        'author_name',
        'slug',
        'biography',
        'avatar',
        'designation',
        'email',
        'website',
        'social_links',
        'is_featured',
        'post_count',
        'status',
    ];

    protected $casts = [
        'status' => 'integer',
        'is_featured' => 'boolean',
        'post_count' => 'integer',
        'social_links' => 'array',
    ];

    /**
     * User associated with this author.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Team members under this author.
     */
    public function teamMembers(): HasMany
    {
        return $this->hasMany(TeamMember::class);
    }
}
