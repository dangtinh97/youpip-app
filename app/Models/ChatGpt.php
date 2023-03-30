<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class ChatGpt extends Model
{
    protected $collection = 'chat_gpt';
    protected $fillable = ['user_id','messages','total_token'];
}
