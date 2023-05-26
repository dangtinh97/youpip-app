<?php

namespace App\Models;



use Jenssegers\Mongodb\Eloquent\Model;

/**
 * @property string $_id
 */
class Work extends Model
{
    protected $collection = 'wm_works';

    protected $fillable = [
        'board_id',
        'job_list_id',
        'title'
    ];
}
