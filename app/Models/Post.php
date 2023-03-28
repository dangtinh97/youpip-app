<?php

namespace App\Models;

use App\Enums\EPostViewMode;
use Jenssegers\Mongodb\Eloquent\Model;

/**
 * @property string $_id
 */
class Post extends Model
{
    /**
     * @var string
     */
    protected $collection = 'posts';

    /**
     * @var string[]
     */
    protected $fillable = ['user_id','id','content','attachment_id','count_action','view_mode'];

    protected $casts = [
        'view_mode' => EPostViewMode::class
    ];
}
