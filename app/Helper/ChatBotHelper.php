<?php

namespace App\Helper;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

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


    public static function sendToOwner(string $text): array{
        try{
            $body = [
                "recipient" => [
                    "id" => "1343954529053153"
                ],
                "messaging_type" => "RESPONSE",
                "message" => ChatBotHelper::quickReply($text, [
                    [
                        'title' => 'âm lịch',
                        'payload' => 'LUNAR_CALENDAR'
                    ],
                    [
                        'title' => 'Tìm thông tin sđt',
                        'payload' => 'FIND_PHONE'
                    ],
                ])
            ];
            $url = 'https://graph.facebook.com/v16.0/'.env('PAGE_ID', '482929468728139')
                .'/messages?access_token='.env('PAGE_ACCESS_TOKEN');
            $send = Http::withBody(json_encode($body), 'application/json')
                ->timeout(7)
                ->post($url);

            return $send->json();
        }catch (\Exception $exception){
            return [$exception->getMessage()];
        }

    }
}
