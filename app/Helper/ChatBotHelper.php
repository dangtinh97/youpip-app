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

    /**
     * @param string $title
     * @param string $subtitle
     * @param array  $buttons
     * @param string $image
     *
     * @return array[]
     */
    static function generic(string $title,string $subtitle,array $buttons,string $image=''): array
    {
        return [
            'attachment' => [
                'type' => 'template',
                'payload' => [
                    'template_type' => 'generic',
                    'elements' => [
                        [
                            'title' => $title,
                            'subtitle' => $subtitle,
                            'image_url' => $image,
                            'buttons' => $buttons
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * @param string $text
     *
     * @return string
     */
    static function removeBadWord(string $text): string
    {
        $badwords = config('badword');
        foreach ($badwords as $key) {
            $text = str_replace($key, '***', $text);
        }

        return $text;
    }
}
