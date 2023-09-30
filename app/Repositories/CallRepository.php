<?php

namespace App\Repositories;

use App\Models\Call;
use Jenssegers\Mongodb\Eloquent\Model;

class CallRepository extends BaseRepository
{
    public function __construct(Call $model)
    {
        parent::__construct($model);
    }
}
