<?php

namespace App\Repositories;

use App\Models\TeamLunchMoney;
use Jenssegers\Mongodb\Eloquent\Model;

class TeamLunchMoneyRepository extends BaseRepository
{
    public function __construct(TeamLunchMoney $model)
    {
        parent::__construct($model);
    }
}
