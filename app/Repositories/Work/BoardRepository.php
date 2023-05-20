<?php

namespace App\Repositories\Work;

use App\Models\Board;
use App\Repositories\BaseRepository;
use Jenssegers\Mongodb\Eloquent\Model;

class BoardRepository extends BaseRepository
{
    /**
     * @param \App\Models\Board $model
     */
    public function __construct(Board $model)
    {
        parent::__construct($model);
    }
}
