<?php

namespace App\Repositories\Chatbot;

use App\Models\CbUser;
use App\Repositories\BaseRepository;

class UserRepository extends BaseRepository
{
    public function __construct(CbUser $model)
    {
        parent::__construct($model);
    }
}
