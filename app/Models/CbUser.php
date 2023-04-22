<?php

namespace App\Models;


use Jenssegers\Mongodb\Eloquent\Model;

/**
 * @property string|null $fbid_connect
 * @property string      $fbid
 * @property string       $status
 */
class CbUser extends Model
{
    protected $collection = 'cb_users';

    protected $fillable = [
        'id',
        'fbid',
        'status',
        'fbid_connect',
        'full_name',
        'block',
        'time_latest'
    ];
}
