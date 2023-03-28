<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\SoftDeletes;

/**
 * @property int $id
 */
class Attachment extends Model
{
    use SoftDeletes;

    /**
     * @var string
     */
    protected $collection = 'attachments';

    /**
     * @var string[]
     */
    protected $fillable = ['id', 'disk', 'path', 'use'];
}
