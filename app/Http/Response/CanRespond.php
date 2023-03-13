<?php

namespace App\Http\Response;

interface CanRespond
{
    /**
     * @return array
     */
    public function toArray(): array;
}
