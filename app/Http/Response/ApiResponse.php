<?php

namespace App\Http\Response;

class ApiResponse implements CanRespond
{
    /**
     * @param int    $status
     * @param string $content
     * @param array  $data
     */
    public function __construct(public int $status=200,public string $content = '',public array $data=[])
    {

    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'status' => $this->status,
            'content' => $this->content,
            'data' => $this->data
        ];
    }
}
