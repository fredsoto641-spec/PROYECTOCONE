<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

#[Fillable([
    'category_id',
    'title',
    'slug',
    'subtitle',
    'location',
    'body',
    'cover_image_url',
    'gallery_image_urls',
    'whatsapp_country_code',
    'whatsapp_number',
    'whatsapp_url',
    'telegram_username',
    'telegram_url',
    'sms_country_code',
    'sms_number',
    'sms_url',
    'tags',
    'is_active',
    'is_vip',
    'published_at',
    'ends_at',
])]
class Post extends Model
{
    use HasFactory;

    public const PUBLIC_PER_PAGE = 20;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'gallery_image_urls' => 'array',
            'tags' => 'array',
            'is_active' => 'boolean',
            'is_vip' => 'boolean',
            'published_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function cards(): HasMany
    {
        return $this->hasMany(PostCard::class)->orderBy('sort_order')->orderBy('id');
    }

    public function isPendingPublication(): bool
    {
        return $this->is_active
            && $this->published_at !== null
            && $this->published_at->isFuture();
    }

    public function isPubliclyVisible(): bool
    {
        return $this->is_active
            && ($this->published_at === null || $this->published_at->isPast())
            && ! $this->isFinished();
    }

    public function isFinished(): bool
    {
        return $this->ends_at !== null && $this->ends_at->isPast();
    }

    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->where(fn (Builder $query) => $query
                ->whereNull('published_at')
                ->orWhere('published_at', '<=', now()))
            ->where(fn (Builder $query) => $query
                ->whereNull('ends_at')
                ->orWhere('ends_at', '>', now()));
    }
}
