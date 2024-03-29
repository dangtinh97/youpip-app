<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

/**
 * @property int    $id
 * @property array  $data
 * @property string $_id
 */
class Board extends Model
{
    protected $collection = 'wm_boards';

    protected $fillable = ['title', 'data', 'id', 'active', 'user_id'];
}
