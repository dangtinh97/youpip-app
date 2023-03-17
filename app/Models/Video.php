<?php

namespace App\Models;



use Jenssegers\Mongodb\Eloquent\Model;

class Video extends Model
{
    /**
     * @var string
     */
    protected $collection = 'videos';

    /**
     * @var string[]
     */
    protected $table = [
        'video_id',
        'title',
        'thumbnail',
        'chanel_name',
        'chanel_url',
        'time_text',
        'published_time',
        'view_count_text',
        'view_count',
        'status',
        'video_play'
    ];
}
