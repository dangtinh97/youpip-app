<?php

namespace App\Enums;

enum EPostViewMode:string
{
    case PUBLIC = 'PUBLIC';
    case PRIVATE = 'PRIVATE';
    case FRIEND = 'FRIEND';
}
