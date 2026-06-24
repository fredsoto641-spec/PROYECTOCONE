<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'post_id',
    'title',
    'color',
    'fill_background',
    'fields',
    'sort_order',
    'is_active',
])]
class PostCard extends Model
{
    protected function casts(): array
    {
        return [
            'fields' => 'array',
            'fill_background' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
