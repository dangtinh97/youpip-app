<?php

namespace App\Models;



use Jenssegers\Mongodb\Eloquent\Model;

class Call extends Model
{
    protected $collection = 'cb_call';

    protected $fillable = ['from_user_id','with_user_id','uuid','status'];
}
