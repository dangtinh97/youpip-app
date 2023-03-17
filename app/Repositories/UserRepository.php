<?php

namespace App\Repositories;

use App\Models\User;
use Jenssegers\Mongodb\Eloquent\Model;

class UserRepository extends BaseRepository
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }
}
