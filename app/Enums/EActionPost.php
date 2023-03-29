<?php

namespace App\Enums;

enum EActionPost:string
{
    case LIKE = 'LIKE';
    case DISLIKE = 'DISLIKE';
    case TYPE_ACTION_REACTION = 'REACTION';
    case TYPE_ACTION_COMMENT = 'COMMENT';
}
