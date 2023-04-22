<?php

namespace App\Helper;

use Illuminate\Support\Arr;

class ChatBotHelper
{
    /**
     * @param string $message
     * @param array  $options
     *
     * @return array
     */
    static function quickReply(string $message, array $options): array
    {
        $quicks = array_map(function ($item) {
            /** @var array $item */
            return [
                'content_type' => 'text',
                'title' => Arr::get($item, 'title'),
                'payload' => Arr::get($item, 'payload'),
                'image_url' => Arr::get($item, 'image_url', '')
            ];
        }, $options);

        return [
            'text' => $message,
            'quick_replies' => $quicks
        ];
    }
}
