<?php

namespace App\Repositories;

use App\Models\View;
use Jenssegers\Mongodb\Eloquent\Model;

class ViewRepository extends BaseRepository
{
    public function __construct(View $model)
    {
        parent::__construct($model);
    }
}
