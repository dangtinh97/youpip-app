<?php

namespace App\Models;



use Jenssegers\Mongodb\Eloquent\Model;

/**
 * @property string $role
 * @property string  $message
 */
class CBChatGpt extends Model
{
    protected $collection = 'cb_chatgpt';
    protected $fillable = [
        'user_id',
        'message',
        'role'
    ];
}
