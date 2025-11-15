<?php

namespace App\Models;

use App\Enums\VoiceNoteItemStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VoiceNoteItem extends Model
{
    protected $fillable = [
        'voice_note_id',
        'description',
        'status',
        'due_date',
    ];

    protected $casts = [
        'status' => VoiceNoteItemStatus::class,
        'due_date' => 'date',
    ];

    public function voiceNote(): BelongsTo
    {
        return $this->belongsTo(VoiceNote::class);
    }
}
