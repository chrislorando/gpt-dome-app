<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\ModelStatus;
use App\Enums\ModelType;

class AiModel extends Model
{
    protected $table = 'models';

    protected $fillable = [
        'id',
        'object',
        'owned_by',
        'status',
        'type',
    ];

    protected $casts = [
        'status' => ModelStatus::class,
        'type' => ModelType::class,
    ];

    public $incrementing = false;
    protected $keyType = 'string';
}
