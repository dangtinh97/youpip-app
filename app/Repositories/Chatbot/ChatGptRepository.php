<?php

namespace App\Repositories\Chatbot;

use App\Models\CBChatGpt;
use App\Repositories\BaseRepository;
use Jenssegers\Mongodb\Eloquent\Model;

class ChatGptRepository extends BaseRepository
{
    public function __construct(CBChatGpt $model)
    {
        parent::__construct($model);
    }
}
