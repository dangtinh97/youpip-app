<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Collection;
use Jenssegers\Mongodb\Eloquent\Model;
use Jenssegers\Mongodb\Relations\HasMany;

/**
 * @property string|null                                    $fbid_connect
 * @property string                                         $fbid
 * @property string                                         $status
 * @property string                                         $block
 * @property-read  \Illuminate\Database\Eloquent\Collection $messagesChatGpt
 * @property int                                          $id
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

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function messagesChatGpt(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CBChatGpt::class, 'user_id', 'id');
    }
}
