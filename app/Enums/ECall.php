<?php
namespace App\Enums;
enum ECall:string {
    case START = 'START';
    case IN_CALL = 'IN_CALL';

    case SEND_NOTIFICATION = "SEND_NOTIFICATION";
}
