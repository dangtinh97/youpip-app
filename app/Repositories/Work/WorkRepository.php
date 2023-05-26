<?php

namespace App\Repositories\Work;

use App\Models\Work;
use App\Repositories\BaseRepository;
use Jenssegers\Mongodb\Eloquent\Model;

class WorkRepository extends BaseRepository
{
    /**
     * @param \App\Models\Work $model
     */
    public function __construct(Work $model)
    {
        parent::__construct($model);
    }
}
