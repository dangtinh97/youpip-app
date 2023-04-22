<?php

namespace App\Models;


use Jenssegers\Mongodb\Eloquent\Model;

class CbUser extends Model
{
    protected $collection = 'cb_users';
    protected $fillable = [
      'fbid',
      'status',
      'fbid_connect',
      'full_name'
    ];
}
