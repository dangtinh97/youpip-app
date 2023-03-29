<?php

namespace App\Repositories;

use App\Models\PostAction;
use Jenssegers\Mongodb\Eloquent\Model;

class PostActionRepository extends BaseRepository
{
    public function __construct(PostAction $model)
    {
        parent::__construct($model);
    }
}
