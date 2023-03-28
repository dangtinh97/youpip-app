<?php

namespace App\Repositories;

use App\Models\Attachment;
use Jenssegers\Mongodb\Eloquent\Model;

class AttachmentRepository extends BaseRepository
{
    public function __construct(Attachment $model)
    {
        parent::__construct($model);
    }
}
