<?php

namespace App\Enums;

enum EStatusApi: int
{
    case SUCCESS = 200;
    case FAIL = 500;
    case AUTHENTICATION = 401;
    case NO_CONTENT = 204;
}
