<?php

namespace App\Http\Response;

use App\Enums\EStatusApi;

class ResponseSuccess extends ApiResponse
{
    /**
     * @param array  $data
     * @param string $content
     */
    public function __construct(public array $data=[],public string $content='Success!')
    {
        parent::__construct(EStatusApi::SUCCESS->value,$this->content,$this->data);
    }
}
