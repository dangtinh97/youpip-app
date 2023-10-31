<?php

namespace App\Models;



use Jenssegers\Mongodb\Eloquent\Model;

class TeamLunchMoney extends Model
{
    protected $collection = 'team_lunch_money';
    protected $fillable = ['date','username','total','paid','note'];
}
