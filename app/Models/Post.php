<?php

namespace App\Models;

use App\Enums\EPostViewMode;
use Jenssegers\Mongodb\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\SoftDeletes;

/**
 * @property string                    $_id
 * @property string                    $content
 * @property \MongoDB\BSON\UTCDateTime $created_at
 * @property int                     $user_id
 */
class Post extends Model
{
    use SoftDeletes;
    /**
     * @var string
     */
    protected $collection = 'posts';

    /**
     * @var string[]
     */
    protected $fillable = ['user_id','id','content','attachment_id','count_action','view_mode','deleted_at'];

    protected $casts = [
        'view_mode' => EPostViewMode::class
    ];
}
