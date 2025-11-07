<?php

namespace App\Enums;

enum ModelType: string
{
    case Text = 'text';
    case Image = 'image';
    case Video = 'video';
    case Audio = 'audio';
    case Other = 'other';
}