<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    /** @use HasFactory<\Database\Factories\ConversationFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'title',
        'user_id',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(ConversationItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeSearch($query, ?string $search)
    {
        if ($search) {
            return $query->where('title', 'like', '%'.$search.'%');
        }

        return $query;
    }
}
