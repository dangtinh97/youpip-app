<?php

namespace App\Models;

use App\Enums\EPostViewMode;
use Jenssegers\Mongodb\Eloquent\Model;

class Post extends Model
{
    /**
     * @var string
     */
    protected $collection = 'posts';

    /**
     * @var string[]
     */
    protected $fillable = ['user_id','id','title','content','image','count_action','url_detail','view_mode'];

    protected $casts = [
        'view_mode' => EPostViewMode::class
    ];
}
