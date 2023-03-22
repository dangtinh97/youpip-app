<?php

namespace App\Repositories;

use App\Models\Log;
use Jenssegers\Mongodb\Eloquent\Model;

class LogRepository extends BaseRepository
{
    public function __construct(Log $model)
    {
        parent::__construct($model);
    }
}
