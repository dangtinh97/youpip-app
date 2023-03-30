<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class Config extends Model
{
    protected $collection = 'configs';
    protected $fillable = ['type','data','show'];
}