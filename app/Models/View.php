<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Jenssegers\Mongodb\Eloquent\Model;

class View extends Model
{
    protected $collection = 'views';
    protected $fillable = ['video_id','user_id','count'];
}
