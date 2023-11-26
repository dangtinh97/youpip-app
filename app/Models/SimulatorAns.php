<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class SimulatorAns extends Model
{
    protected $collection = 'simulator_ans';
    protected $fillable = ['question_id','result_0','result_1','result_2','result_3','result_4','result_5','time','duration','score'];
}
