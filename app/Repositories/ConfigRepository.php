<?php

namespace App\Repositories;

use App\Models\Config;
use Jenssegers\Mongodb\Eloquent\Model;

class ConfigRepository extends BaseRepository
{
    public function __construct(Config $model)
    {
        parent::__construct($model);
    }
}