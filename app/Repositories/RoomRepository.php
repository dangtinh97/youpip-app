<?php

namespace App\Repositories;

use App\Models\Room;
use Jenssegers\Mongodb\Eloquent\Model;

class RoomRepository extends BaseRepository
{
    public function __construct(Room $model)
    {
        parent::__construct($model);
    }
}