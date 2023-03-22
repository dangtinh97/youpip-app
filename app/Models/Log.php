<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Jenssegers\Mongodb\Eloquent\Model;

class Log extends Model
{
    use HasFactory;
    protected $collection = 'logs';
    protected $fillable = ['type','line','message','file','data'];
}
