<?php

namespace App\Services;

use App\Http\Response\ApiResponse;
use App\Http\Response\ResponseSuccess;
use Illuminate\Support\Arr;
use MongoDB\BSON\ObjectId;

class VtvGoService
{
    public function __construct()
    {
    }

    /**
     * @return \App\Http\Response\ApiResponse
     */
    public function listChannel(): ApiResponse
    {
        $channels = [
            [
                'path' => 'xem-truc-tuyen-kenh-vtv1-1.html',
                'thumbnail' => 'https://liftlearning.com/wp-content/uploads/2020/09/default-image.png',
                'title' => 'VTV 1',
                'chanel_name' => 'VTV 1'
            ],
            [
                'path' => 'xem-truc-tuyen-kenh-vtv2-2.html',
                'thumbnail' => 'https://liftlearning.com/wp-content/uploads/2020/09/default-image.png',
                'title' => 'VTV 2',
                'chanel_name' => 'VTV 2'
            ],
            [
                'path' => 'xem-truc-tuyen-kenh-vtv3-3.html',
                'thumbnail' => 'https://liftlearning.com/wp-content/uploads/2020/09/default-image.png',
                'title' => 'VTV 3',
                'chanel_name' => 'VTV 3'
            ]
        ];

        $output = [];
        foreach ($channels as $channel) {
            $output[] = [
                'video_oid' => (new ObjectId())->__toString(),
                'last_oid' => (new ObjectId())->__toString(),
                'video_id' => Arr::get($channel, 'chanel_name'),
                'thumbnail' => (string)Arr::get($channel, 'thumbnail'),
                'title' => 'Xem truyền hình trực tuyến '.(string)Arr::get($channel, 'title'),
                'time_text' => '--:--',
                'view_count_text' => 0,
                'chanel_name' => Arr::get($channel, 'chanel_name'),
                'chanel_url' => '',
                'published_time' => 'NOW'
            ];
        }

        return new ResponseSuccess([
            'list' => $output
        ]);
    }
}
