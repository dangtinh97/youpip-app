<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

/**
 * @property string $room_id
 * @property string $_id
 * @property int    $id
 * @property int[]  $join
 */
class Room extends Model
{
    protected $collection = 'rooms';

    protected $fillable = ['user_id_created', 'join', 'last_message','id'];
}