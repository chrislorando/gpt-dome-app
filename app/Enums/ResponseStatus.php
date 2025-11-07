<?php

namespace App\Enums;

enum ResponseStatus: string
{
    case Created = 'created';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Failed = 'failed';
    case Incomplete = 'incomplete';
    case Added = 'added';
}