<?php

namespace App\Models;

use App\Models\Traits\HasStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamMember extends Model
{
    use HasFactory, HasStatus;

    protected $fillable = [
        'author_id',
        'name',
        'slug',
        'role_title',
        'biography',
        'photo',
        'email',
        'social_links',
        'sort_order',
        'status',
    ];

    protected $casts = [
        'status' => 'integer',
        'sort_order' => 'integer',
        'social_links' => 'array',
    ];

    /**
     * Author this team member belongs to.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class);
    }

    public function getPhotoUrlAttribute(): string
    {
        return \App\Support\Media::url($this->photo, \App\Support\Media::avatar($this->name));
    }

    public function social(string $key): ?string
    {
        $links = $this->social_links ?? [];

        return !empty($links[$key]) ? $links[$key] : null;
    }
}
