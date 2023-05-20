<?php

namespace App\Models;


use Jenssegers\Mongodb\Eloquent\Model;

class Board extends Model
{
    protected $collection = 'wm_boards';
    protected $fillable = ['title','data','id'];
}
