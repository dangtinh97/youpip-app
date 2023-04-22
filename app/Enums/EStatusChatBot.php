<?php

namespace App\Enums;

enum EStatusChatBot: string
{
    case FREE = "FREE";
    case BUSY = "BUSY";
    case WAIT = "WAIT";
}
