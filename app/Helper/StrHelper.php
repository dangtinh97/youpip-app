<?php

namespace App\Helper;

use MongoDB\BSON\ObjectId;

class StrHelper
{
    /**
     * @param string $value
     *
     * @return bool
     */
    public static function isObjectId(string $value): bool
    {
        try {
            new ObjectId($value);

            return true;
        } catch (\Exception $exception) {
            return false;
        }
    }
}
