<?php

namespace App\Models;

use App\Enums\ResponseStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VoiceNote extends Model
{
    use HasUuids;

    protected $fillable = [
        'title',
        'file_name',
        'file_size',
        'file_url',
        'transcript',
        'response',
        'tags',
        'duration',
        'status',
    ];

    protected $casts = [
        'tags' => 'array',
        'status' => ResponseStatus::class,
    ];

    public function items(): HasMany
    {
        return $this->hasMany(VoiceNoteItem::class);
    }
}
