<?php

namespace App\Models;


use Jenssegers\Mongodb\Eloquent\Model;

/**
 * @property int   $user_id
 * @property string $_id
 */
class PostAction extends Model
{
    protected $collection = 'post_actions';
    protected $fillable = ['post_id','user_id','type','content'];
}
