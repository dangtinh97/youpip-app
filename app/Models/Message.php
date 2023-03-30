<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

/**
 * @property int    $from_user_id
 * @property string $message
 * @property string $_id
 * @property \MongoDB\BSON\UTCDateTime  $created_at
 */
class Message extends Model
{
    /**
     * @var string
     */
    protected $collection = 'messages';

    /**
     * @var string[]
     */
    protected $fillable = ['from_user_id','message','type','time_view','room_id'];
}
