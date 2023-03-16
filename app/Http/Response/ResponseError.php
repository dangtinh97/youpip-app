<?php

namespace App\Http\Response;

use App\Enums\EStatusApi;

class ResponseError extends ApiResponse
{
    /**
     * @param int    $status
     * @param string $content
     * @param array  $data
     */
    public function __construct(public int $status = 500,public string $content='Error!',public array $data=[])
    {
        parent::__construct($this->status,$this->content,$this->data);
    }
}
